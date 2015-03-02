# Research

## Overview

This section is to help gather research about different APIs for custom field plugins and others with their own Fields
APIs for their forms.

* [Advanced Custom Fields](research/advanced-custom-fields.md)
* [Meta Box](research/meta-box.md)
* [Settings API](research/settings-api.md)

## What makes a Fields API?

A Fields API provides the ability to register fields to any given form. With custom fields, that often becomes the Post
Editor screen or other add/edit screens for WP objects. With forms plugins, that can be registering forms themselves or
extending form structures.

The goal of a Fields API is to avoid writing markup and to abstract those things so they offer the most comprehensive
customizations in how those forms appear in different contexts.

Some Fields APIs focus on data structures similar to defining a database table and the corresponding columns. Other
Fields APIs focus entirely on the form fields to output with some amount of handling for data saving included.