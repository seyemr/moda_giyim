#!/bin/sh
set -eu

project_root=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
release_dir="$project_root/dist"

if ! command -v zip >/dev/null 2>&1; then
	printf '%s\n' "Error: the zip utility is required to package the WordPress theme and plugin." >&2
	exit 1
fi

for source in \
	"$project_root/wp-content/themes/moda-atelier/style.css" \
	"$project_root/wp-content/themes/moda-atelier/functions.php" \
	"$project_root/wp-content/plugins/moda-store-admin/moda-store-admin.php"
do
	if [ ! -f "$source" ]; then
		printf 'Error: required WordPress source file is missing: %s\n' "$source" >&2
		exit 1
	fi
done

mkdir -p "$release_dir"

(
	cd "$project_root/wp-content/themes"
	zip -qr -FS "$release_dir/moda-atelier.zip" moda-atelier
)

(
	cd "$project_root/wp-content/plugins"
	zip -qr -FS "$release_dir/moda-store-admin.zip" moda-store-admin
)

printf 'Created WordPress install packages in %s\n' "$release_dir"
