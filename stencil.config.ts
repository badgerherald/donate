import { Config } from '@stencil/core';
import { sass } from '@stencil/sass';

export const config: Config = {
  namespace: 'donate',
  taskQueue: 'async',
  outputTargets: [
    {
      type: 'dist',
      esmLoaderPath: '../loader',
      copy: [
        { src: '**/*.php' },
        { src: 'assets/' },
        ] 
    }
  ],
  plugins: [
		sass()
	],
};


