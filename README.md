# Dokuwiki (Rest) Api Plugin

[![Build Status](https://travis-ci.org/gerardnico/dokuwiki-plugin-api.svg?branch=master)](https://travis-ci.org/gerardnico/dokuwiki-plugin-api)

## About

This is a Dokuwiki plugin that implements a rest API for Dokuwiki in order to use DokuWiki as a backend Content Management System (CMS).


## Permissions
All requests are made with the `public` permissions (ie [@ALL group](https://www.dokuwiki.org/acl))

## Endpoints

### pages
`pages` returns a list of all pages
 
```
http://localhost:81/lib/exe/ajax.php?call=api&fn=pages&limit=10
```
Request Query parameters:
  * `limit` : the max number of pages (known also under [pagename](https://www.dokuwiki.org/pagename))


### page

`page` returns much more information on a page than [pages](#pages)

```
http://localhost:81/lib/exe/ajax.php?call=api&fn=page
```

Query parameters:
  * `id` : the page id (known also under [pagename](https://www.dokuwiki.org/pagename))

## Configuration

The following configuration have an impact on the export.
As they are defined as constant inside DokuWiki, you can't update them on the fly.
You need to change them in your configuration.

### Relative URL

If you want relative URL in the exported HTML. See https://www.dokuwiki.org/config:canonical

### Base Directory

See https://www.dokuwiki.org/config:basedir The DOKU_BASE constant. 

Used in the `wl` function of the `common.php` file to create a link.


