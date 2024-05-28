// webpack.config.js
const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/js/app.js')
    .addStyleEntry('main', './assets/scss/main.scss')
    .splitEntryChunks()
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableSassLoader()
;

// Désactiver le versioning en mode développement
if (!Encore.isProduction()) {
    Encore.configureFilenames({
        js: '[name].js',
        css: '[name].css',
    });
} else {
    // Activer le versioning en mode production
    Encore.enableVersioning();
}

module.exports = Encore.getWebpackConfig();
