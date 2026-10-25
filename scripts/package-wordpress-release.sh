#!/bin/sh
set -eu

project_root=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
release_dir="$project_root/dist"

if ! command -v zip >/dev/null 2>&1; then
	printf '%s\n' "Error: the zip utility is required to package the WordPress theme and plugins." >&2
	exit 1
fi

for source in \
	"$project_root/wp-content/themes/moda-atelier/style.css" \
	"$project_root/wp-content/themes/moda-atelier/functions.php" \
	"$project_root/wp-content/plugins/moda-store-admin/moda-store-admin.php" \
	"$project_root/wp-content/plugins/moda-pwa/moda-pwa.php" \
	"$project_root/wp-content/plugins/moda-pwa/assets/pwa.js" \
	"$project_root/wp-content/plugins/moda-pwa/assets/sw.js" \
	"$project_root/wp-content/plugins/moda-pwa/assets/pwa.css" \
	"$project_root/wp-content/plugins/moda-pwa/assets/icon-192.png" \
	"$project_root/wp-content/plugins/moda-pwa/assets/icon-512.png" \
	"$project_root/wp-content/plugins/moda-pwa/assets/apple-touch-icon.png" \
	"$project_root/wp-content/plugins/moda-checkout-acceptance/moda-checkout-acceptance.php" \
	"$project_root/wp-content/plugins/moda-support/moda-support.php" \
	"$project_root/wp-content/plugins/moda-support/assets/support.js" \
	"$project_root/wp-content/plugins/moda-support/assets/support.css" \
	"$project_root/wp-content/plugins/moda-commerce-tools/moda-commerce-tools.php" \
	"$project_root/wp-content/plugins/moda-commerce-tools/assets/commerce.js" \
	"$project_root/wp-content/plugins/moda-commerce-tools/assets/commerce.css"
do
	if [ ! -f "$source" ]; then
		printf 'Error: required WordPress release source file is missing: %s\n' "$source" >&2
		exit 1
	fi
done

mkdir -p "$release_dir"
rm -f "$release_dir"/moda-*.zip

(
	cd "$project_root/wp-content/themes"
	zip -qr "$release_dir/moda-atelier.zip" moda-atelier
)

for plugin in moda-store-admin moda-pwa moda-checkout-acceptance moda-support moda-commerce-tools
do
	(
		cd "$project_root/wp-content/plugins"
		zip -qr "$release_dir/$plugin.zip" "$plugin"
	)
done

printf 'Created WordPress install packages in %s\n' "$release_dir"
