import { Config } from '@stencil/core';
import { sass } from '@stencil/sass';

export const config : Config = {
	srcDir: 'src',
	globalStyle: 'src/assets/sass/foundations.scss',

	outputTargets: [
		{ type: 'www', serviceWorker: null }
	],
	plugins: [
		sass()
	],
};

exports.devServer = {
	root: "www",
	watchGlob: ["./src/client/**"]
}
