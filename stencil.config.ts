import { Config } from "@stencil/core"
import { sass } from "@stencil/sass"

export const config: Config = {
	namespace: "donate",
	outputTargets: [
		{
			type: "dist",
			esmLoaderPath: "../loader",
			copy: [
				{ src: "lib/**/*" },
				{ src: "lib/**/*.php" },
				{ src: "functions.php" },
			],
		},
		{
			type: "www",
			buildDir: "app",
			dir: "wp-content/themes/donate-test/",
			copy: [
				{ src: "lib/**/*" },
				{ src: "functions.php" },
				{ src: "index.php" },
				{ src: "style.css" },
			],
		},
	],
	plugins: [sass({ injectGlobalPaths: ["src/global/sass/foundations.scss"] })],
}
