## Badger Herald Donation Stack

This little set of web components runs the front-end inline donation form on badgerherald.com/donate

Made with [stencil.js](https://stenciljs.com/)

### Running locally

For convience, docker-compose can be used to run a local WordPress instance.

Steps:

1. Copy dev.env to .env and define stripe credentials and other settings
2. Install docker and docker-compose
3. Run `docker-compose up`
4. Navigate to http://localhost:8000 in a browser
5. Follow the steps to install a fresh WordPress site (use any name, user, password)
6. Enable the WordPress theme donate-test
7. Enable pretty permalinks to turn on the WP REST API
8. (Optional) Install the sendgrid plugin and set a license key to test emails

### Troubleshooting

Fixing DNS issues with Docker:
https://development.robinwinslow.uk/2016/06/23/fix-docker-networking-dns/#the-permanent-system-wide-fix
