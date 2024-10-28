module.exports = {
	input: './src/application.js',
	output: './dist/application.bundle.js',
	namespace: 'BX.KB',
	browserslist: true,
    plugins: {
        resolve: true,
    },
    minification: true,
};
