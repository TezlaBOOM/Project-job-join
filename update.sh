#!/usr/bin/env bash

# ==============================================================================
# Skrypt automatycznej aktualizacji aplikacji: Portal Rekrutacyjny Urzędu Miasta
# ==============================================================================
# Pobiera aktualizacje z repozytorium GitHub określonego w pliku .env:
#   GITHUB_REPOSITORY=https://github.com/TezlaBOOM/Project-job-join.git
#   GITHUB_BRANCH=main
#   GITHUB_TOKEN= (opcjonalny token PAT dla prywatnych repozytoriów)
#
# Użycie:
#   ./update.sh                     - pełna aktualizacja z GitHub
#   ./update.sh --no-git            - aktualizacja lokalna (bez pobierania z Git)
#   ./update.sh --branch <nazwa>    - pobranie z innej gałęzi (np. staging, develop)
#   ./update.sh --repo <url>        - nadpisanie adresu repozytorium
#   ./update.sh --seed              - uruchomienie seederów po migracji
#   ./update.sh --help              - pomoc
# ==============================================================================

set -eo pipefail
export GIT_TERMINAL_PROMPT=0

# Kolory terminala
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m' # No Color

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_DIR"

SKIP_GIT=false
RUN_SEED=false
CLI_BRANCH=""
CLI_REPO=""

# Funkcja pobierająca wartość zmiennej z pliku .env
get_env_val() {
    local key="$1"
    local default_val="$2"
    if [ -f ".env" ]; then
        local line
        line=$(grep -E "^${key}=" .env | tail -n 1 | cut -d '=' -f2- | tr -d '\r')
        if [ -n "$line" ]; then
            # Usunięcie otaczających cudzysłowów
            echo "$line" | sed -e 's/^"//' -e 's/"$//' -e "s/^'//" -e "s/'$//"
            return
        fi
    fi
    echo "$default_val"
}

# Przetwarzanie argumentów wiersza poleceń
while [[ $# -gt 0 ]]; do
    case "$1" in
        --no-git)
            SKIP_GIT=true
            shift
            ;;
        --seed)
            RUN_SEED=true
            shift
            ;;
        --branch)
            CLI_BRANCH="$2"
            shift 2
            ;;
        --repo)
            CLI_REPO="$2"
            shift 2
            ;;
        --help|-h)
            echo -e "${BOLD}Skrypt aktualizacji Portalu Rekrutacyjnego z GitHub${NC}"
            echo ""
            echo "Dostępne opcje:"
            echo "  --no-git           Pomiń krok pobierania z Git (np. w środowisku lokalnym)"
            echo "  --branch <nazwa>   Wybierz gałąź Git (domyślnie z .env lub 'main')"
            echo "  --repo <url>       Nadpisz adres repozytorium GitHub z .env"
            echo "  --seed             Uruchom seedery po wykonaniu migracji (db:seed)"
            echo "  --help, -h         Wyświetl ten komunikat"
            echo ""
            echo "Konfiguracja w pliku .env:"
            echo "  GITHUB_REPOSITORY=https://github.com/TezlaBOOM/Project-job-join.git"
            echo "  GITHUB_BRANCH=main"
            echo "  GITHUB_TOKEN= (opcjonalny Personal Access Token dla prywatnych repozytoriów)"
            exit 0
            ;;
        *)
            echo -e "${YELLOW}Nieznany parametr: $1 (użyj --help)${NC}"
            shift
            ;;
    esac
done

# Odczyt konfiguracji repozytorium
ENV_REPO=$(get_env_val "GITHUB_REPOSITORY" "")
if [ -z "$ENV_REPO" ]; then
    ENV_REPO=$(get_env_val "GITHUB_REPO_URL" "")
fi

ENV_BRANCH=$(get_env_val "GITHUB_BRANCH" "main")
ENV_TOKEN=$(get_env_val "GITHUB_TOKEN" "")

TARGET_REPO="${CLI_REPO:-$ENV_REPO}"
TARGET_BRANCH="${CLI_BRANCH:-$ENV_BRANCH}"

echo -e "${CYAN}================================================================${NC}"
echo -e "${BOLD}${BLUE}  🏛️  PORTAL REKRUTACYJNY UM - AUTOMATYCZNA AKTUALIZACJA${NC}"
echo -e "${CYAN}================================================================${NC}"
echo -e "Katalog projektu:  ${BOLD}$PROJECT_DIR${NC}"
echo -e "Data rozpoczęcia:  ${BOLD}$(date '+%Y-%m-%d %H:%M:%S')${NC}"
if [ "$SKIP_GIT" = false ]; then
    echo -e "Repozytorium Git:  ${BOLD}${TARGET_REPO:-brak}${NC} [źródło: .env]"
    echo -e "Gałąź (branch):    ${BOLD}$TARGET_BRANCH${NC}"
fi
echo ""

STASHED=false

# Funkcja przywracająca działanie serwisu w razie błędu lub przerwania
cleanup_on_exit() {
    local exit_code=$?
    if [ $exit_code -ne 0 ]; then
        echo ""
        echo -e "${YELLOW}[INFO] Przywracanie działania aplikacji (php artisan up)...${NC}"
        php artisan up || true
        if [ "$STASHED" = true ]; then
            echo -e "${YELLOW}[INFO] Przywracanie zmian roboczych z pamięci podręcznej (git stash pop)...${NC}"
            git stash pop >/dev/null 2>&1 || true
        fi
        echo -e "${RED}[KONIEC] Aktualizacja przerwana. Sprawdź powyższe komunikaty błędów.${NC}"
    fi
}

trap cleanup_on_exit EXIT

# 1. Weryfikacja środowiska PHP
echo -e "${YELLOW}[1/8] Sprawdzanie środowiska wykonawczego...${NC}"
if ! command -v php >/dev/null 2>&1; then
    echo -e "${RED}[BŁĄD] Nie odnaleziono polecenia 'php'. Dodaj PHP do zmiennej PATH.${NC}"
    exit 1
fi
echo -e "       PHP: $(php -r 'echo PHP_VERSION;') ($(php -r 'echo PHP_SAPI;'))"

# 2. Włączenie trybu konserwacji
echo -e "${YELLOW}[2/8] Aktywacja trybu konserwacji (Maintenance mode)...${NC}"
php artisan down --refresh=15 --secret="rekrutacja-bypass-key" || true
echo -e "       ${GREEN}Aplikacja jest w trybie konserwacji.${NC}"

# 3. Pobranie najnowszych zmian z GitHub
echo -e "${YELLOW}[3/8] Pobieranie aktualizacji z GitHub...${NC}"
if [ "$SKIP_GIT" = true ]; then
    echo -e "       ${CYAN}Pominięto krok pobierania z Git (flaga --no-git).${NC}"
else
    if ! command -v git >/dev/null 2>&1; then
        echo -e "${RED}[BŁĄD] Polecenie 'git' nie jest zainstalowane w systemie.${NC}"
        exit 1
    fi

    if [ -z "$TARGET_REPO" ]; then
        echo -e "${RED}[BŁĄD] Brak zdefiniowanego adresu repozytorium w pliku .env!${NC}"
        echo -e "${YELLOW}       Ustaw zmienną GITHUB_REPOSITORY w .env, np.:${NC}"
        echo -e "       GITHUB_REPOSITORY=https://github.com/TezlaBOOM/Project-job-join.git"
        exit 1
    fi

    # Przygotowanie uwierzytelnionego URL dla prywatnych repozytoriów
    FETCH_URL="$TARGET_REPO"
    DISPLAY_URL="$TARGET_REPO"

    if [ -n "$ENV_TOKEN" ] && [[ "$TARGET_REPO" =~ ^https:// ]]; then
        # Wstrzyknięcie tokenu do żądania HTTPS
        FETCH_URL=$(echo "$TARGET_REPO" | sed -E "s#https://([^@]+@)?#https://${ENV_TOKEN}@#")
        DISPLAY_URL=$(echo "$TARGET_REPO" | sed -E "s#https://([^@]+@)?#https://***@#")
    fi

    # Inicjalizacja repozytorium jeśli nie istnieje
    if [ ! -d ".git" ]; then
        echo -e "       Inicjalizacja lokalnego repozytorium Git..."
        git init
        git remote add origin "$TARGET_REPO"
    fi

    # Synchronizacja adresu remote origin
    CURRENT_ORIGIN=$(git config --get remote.origin.url 2>/dev/null || echo "")
    if [ "$CURRENT_ORIGIN" != "$TARGET_REPO" ]; then
        echo -e "       Ustawianie remote origin na: ${BOLD}$TARGET_REPO${NC}"
        if [ -n "$CURRENT_ORIGIN" ]; then
            git remote set-url origin "$TARGET_REPO"
        else
            git remote add origin "$TARGET_REPO"
        fi
    fi

    # Zabezpieczenie ewentualnych lokalnych modyfikacji przed nadpisaniem
    if ! git diff --quiet || ! git diff --cached --quiet; then
        echo -e "       ${YELLOW}Wykryto lokalne zmiany w plikach. Zapisywanie do pamięci tymczasowej (git stash)...${NC}"
        git stash push -m "updater_auto_stash_$(date +%s)" >/dev/null 2>&1 || true
        STASHED=true
    fi

    echo -e "       Pobieranie zmian z: ${BOLD}$DISPLAY_URL${NC} (gałąź: ${BOLD}$TARGET_BRANCH${NC})"
    
    # Pobranie z Git
    if git fetch "$FETCH_URL" "$TARGET_BRANCH" --depth=50 2>.git_fetch_err; then
        echo -e "       Scalanie najnowszego kodu..."
        git merge FETCH_HEAD --no-edit -m "Automatyczna aktualizacja z GitHub" || {
            echo -e "${YELLOW}       Próba dokończenia scalenia z zachowaniem lokalnych plików...${NC}"
            git merge --abort >/dev/null 2>&1 || true
            git pull origin "$TARGET_BRANCH" --no-edit || true
        }
        echo -e "       ${GREEN}Kod został pomyślnie zaktualizowany z GitHub.${NC}"
        echo -e "       Bieżący commit: ${BOLD}$(git rev-parse --short HEAD 2>/dev/null || echo 'brak')${NC}"
    else
        FETCH_ERR=$(cat .git_fetch_err 2>/dev/null || echo "Błąd połączenia")
        rm -f .git_fetch_err
        echo -e "${RED}[BŁĄD] Nie udało się pobrać aktualizacji z GitHub!${NC}"
        echo -e "       Szczegóły: $FETCH_ERR"
        if [ -z "$ENV_TOKEN" ]; then
            echo -e "${YELLOW}       Wskazówka: Jeśli repozytorium jest prywatne, dodaj do pliku .env:${NC}"
            echo -e "       ${BOLD}GITHUB_TOKEN=ghp_twoj_personal_access_token${NC}"
            echo -e "       Lub użyj klucza SSH: GITHUB_REPOSITORY=git@github.com:TezlaBOOM/Project-job-join.git"
        fi
        exit 1
    fi
    rm -f .git_fetch_err

    # Przywrócenie lokalnych zmian, jeśli były zabezpieczone
    if [ "$STASHED" = true ]; then
        echo -e "       Przywracanie lokalnych modyfikacji (git stash pop)..."
        git stash pop >/dev/null 2>&1 || true
    fi
fi

# 4. Instalacja / aktualizacja zależności PHP (Composer)
echo -e "${YELLOW}[4/8] Aktualizacja zależności PHP (Composer)...${NC}"
if command -v composer >/dev/null 2>&1; then
    COMPOSER_BIN="composer"
elif [ -f "./composer.phar" ]; then
    COMPOSER_BIN="php ./composer.phar"
else
    COMPOSER_BIN="composer"
fi

$COMPOSER_BIN install --no-interaction --prefer-dist --optimize-autoloader
echo -e "       ${GREEN}Zależności PHP zaktualizowane.${NC}"

# 5. Wykonanie migracji bazy danych
echo -e "${YELLOW}[5/8] Wykonywanie migracji bazy danych...${NC}"
php artisan migrate --force

if [ "$RUN_SEED" = true ]; then
    echo -e "       Uruchamianie seederów bazodanowych (--seed)..."
    php artisan db:seed --force
fi
echo -e "       ${GREEN}Struktura bazy danych jest aktualna.${NC}"

# 6. Kompilacja assetów frontendowych (Vite / NPM)
echo -e "${YELLOW}[6/8] Kompilacja zasobów frontendowych (NPM / Vite)...${NC}"
if command -v npm >/dev/null 2>&1; then
    if [ -f "package-lock.json" ]; then
        npm ci || npm install
    else
        npm install
    fi
    npm run build
    if [ -f "resources/css/app.css" ]; then
        mkdir -p public/css
        cp resources/css/app.css public/css/app.css
    fi
    echo -e "       ${GREEN}Zasoby frontendowe skompilowane pomyślnie.${NC}"
else
    echo -e "${YELLOW}       Ostrzeżenie: Polecenie 'npm' nie zostało znalezione w PATH. Pomijanie budowy assetów.${NC}"
fi

# 7. Czyszczenie i optymalizacja pamięci podręcznej (Cache / Route / View)
echo -e "${YELLOW}[7/8] Odświeżanie pamięci podręcznej (Cache & Optimizations)...${NC}"
php artisan optimize:clear
php artisan storage:link || true
php artisan view:cache || true

if [ "$(php -r 'echo env("APP_ENV");')" = "production" ]; then
    php artisan config:cache || true
    php artisan route:cache || true
fi

php artisan queue:restart || true
echo -e "       ${GREEN}Pamięć podręczna zoptymalizowana.${NC}"

# 8. Wyłączenie trybu konserwacji
echo -e "${YELLOW}[8/8] Przywracanie normalnego działania serwisu (php artisan up)...${NC}"
php artisan up
echo -e "       ${GREEN}Aplikacja jest ponownie online!${NC}"

echo ""
echo -e "${CYAN}================================================================${NC}"
echo -e "${BOLD}${GREEN}  ✅  AKTUALIZACJA ZAKOŃCZONA SUKCESEM!${NC}"
echo -e "${CYAN}================================================================${NC}"
echo -e "Data zakończenia: ${BOLD}$(date '+%Y-%m-%d %H:%M:%S')${NC}"
echo ""
