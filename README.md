# Dokuwiki Rest Api Plugin

[![Build Status](https://travis-ci.org/gerardnico/dokuwiki-plugin-restapi.svg?branch=master)](https://travis-ci.org/gerardnico/dokuwiki-plugin-restapi)

## About

This is a Dokuwiki plugin that implements a rest API for Dokuwiki.

## Configuration

The following configuration have an impact on the export.
As they are defined as constant inside DokuWiki, you can't update them on the fly.
You need to change them in your configuration.

### Relative URL

See https://www.dokuwiki.org/config:canonical

### Base Directory

See https://www.dokuwiki.org/config:basedir The DOKU_BASE constant. 

Used in the `wl` function of the `common.php` file to create a link.


