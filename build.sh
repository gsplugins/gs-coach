echo "$(tput setaf 6)" &&

echo 'Building production version...' &&

npm run production
echo -ne 'Production version created......              (30%)\r'

rm -rf build
mkdir -p build/gs-coach #multiple folder creation

echo -ne 'Cleanup and building files started........            (40%)\r'

rsync -r --exclude '.git' --exclude '.svn' --exclude 'build' --exclude 'node_modules' --exclude 'dev' --exclude '.vscode' . build/gs-coach/

echo -ne 'Files copied............        (60%)\r'

rm -rf build/gs-coach/mix-manifest.json &&
rm -rf build/gs-coach/package.json &&
rm -rf build/gs-coach/package-lock.json &&
rm -rf build/gs-coach/webpack.mix.js &&
rm -rf build/gs-coach/.babelrc &&
rm -rf build/gs-coach/.gitignore &&
find . -type f -name '*.DS_Store' -ls -delete &&
rm -rf build/gs-coach/.AppleDouble &&
rm -rf build/gs-coach/.LSOverride &&
rm -rf build/gs-coach/.Trashes &&
rm -rf build/gs-coach/.AppleDB &&
rm -rf build/gs-coach/.idea &&
rm -rf build/gs-coach/build.sh &&
rm -rf build/gs-coach/yarn.lock &&
rm -rf build/gs-coach/composer.json &&
rm -rf build/gs-coach/composer.lock &&
rm -rf build/gs-coach/task.txt &&

rm -rf build/gs-coach/includes/integrations/assets/divi/divi-builder.js &&
rm -rf build/gs-coach/includes/integrations/assets/divi/divi-frontend.js &&
rm -rf build/gs-coach/includes/integrations/assets/elementor/elementor-preview.js &&
rm -rf build/gs-coach/includes/integrations/assets/gutenberg/gutenberg-widget.js &&
rm -rf build/gs-coach/includes/gs-common-pages/assets/gs-plugins-common-pages.scss &&

find . -type f -name '*.LICENSE.txt' -ls -delete &&

echo -ne 'Creating gs-coach.zip file................    (80%)'

cd build
zip -r gs-coach.zip gs-coach/.
rm -r gs-coach

echo -ne 'Congratulations... Successfully done....................(100%)'

npm run development
echo -ne 'Development version restored....................(100%)\r'

echo "$(tput setaf 2)" &&
echo "Clean process completed!"
echo "$(tput sgr0)"