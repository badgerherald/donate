## Badger Herald Donation Stack

This little set of web components runs the front-end inline donation form on badgerherald.com/donate

Made with [stencil.js](https://stenciljs.com/)

### Running locally

For convience, docker-compose can be used to run a local WordPress instance.

Steps:
1. copy dev.env to .env and define your stripe credentials
2. Install docker and docker-compose
3. Run `docker-compose up`
4. Navigate to http://localhost:8000 in a browser
5. Follow the steps to install a fresh WordPress site (use any name, user, password)
6. Enable the WordPress theme donate-test

