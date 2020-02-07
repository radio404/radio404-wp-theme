const path = require("path"),
    UglifyJSPlugin = require("uglifyjs-webpack-plugin"),
    MiniCssExtractPlugin = require("mini-css-extract-plugin"),
    OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");
module.exports = {
    //mode: "development",
    entry: {
        "admin-schedules-sync": "./js/src/admin/schedules-sync.js",
        "admin-tracks-sync": "./js/src/admin/tracks-sync.js",
    },
    output: {
        path: path.resolve(__dirname, "dist"),
        //filename: "bundle.js"
    },
    optimization: {
        minimizer: [new UglifyJSPlugin({
            sourceMap: false
        }), new OptimizeCSSAssetsPlugin({})]
    },
    plugins: [new MiniCssExtractPlugin({
        filename: "style.css"
    })],
    module: {
        rules: [{
            test: /\.js$/,
            exclude: /(node_modules)/,
            use: {
                loader: "babel-loader",
                options: {
                    presets: ["env"]
                }
            }
        }, {
            test: /\.s[ac]ss$/i,
            use: [
                // Creates `style` nodes from JS strings
                'style-loader',
                // Translates CSS into CommonJS
                'css-loader',
                // Compiles Sass to CSS
                'sass-loader',
            ]
        },{
            test: /\.css$/i,
            use: [
                // Creates `style` nodes from JS strings
                'style-loader',
                // Translates CSS into CommonJS
                'css-loader',
            ]
        }, {
            test: /\.(png|svg|jpe?g|gif)$/i,
            use: [{
                loader: "file-loader"
            }]
        }]
    },
    watch: true
};