const path = require('path');

module.exports = {
  entry: {
    'abwc-ajax-cart': './assets/js/abwc-ajax-cart.js',
    'abwc-ajax-variation-cart': './assets/js/abwc-ajax-variation-cart.js',
    'abwc-ajax-cart-admin': './assets/js/abwc-ajax-cart-admin.js'
  },
  output: {
    path: path.resolve(__dirname, 'assets/js/dist'),
    filename: '[name].min.js'
  },
  module: {
    rules: [
      {
        test: /\.m?js$/,
        exclude: /(node_modules|bower_components)/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: [
              [
                '@babel/preset-env',
                {
                  useBuiltIns: 'usage',
                  corejs: 3
                }
              ]
            ]
          }
        }
      }
    ]
  },
  devtool: 'source-map',
  externals: {
    jquery: 'jQuery'
  }
};

