module.exports = {
  devServer: {
    port: 8081,
    host: '0.0.0.0',
    public: 'localhost:8081',
    compress: true,
    proxy: {
      '/api': {
        target: 'http://nginx',
        changeOrigin: true
      }
    }
  }
};
