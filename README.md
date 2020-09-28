# Sarsapirilla

A better Lorem Ipsum for images

See [nomanic.biz/Sarsapirilla](https://www.nomanic.biz/Sarsapirilla/) for complete docs and demos.

## Install

Link directly to files.

``` html
<script src="path/to/Sarsapirilla.min.js"></script>
```

## Usage

Initialize Sarsapirilla on an IMG element.

``` html
<IMG class="color-input" SASP="[obj|car][random][nocache]"/>
```

or DIV

``` html
<DIV class="color-input" SASP="[obj|car][random][nocache]"></DIV>
```

### Initialize with JavaScript

``` js
// use selector string to initialize on single element
Sarsapirilla.fill(document.querySelector('.sasp'));

// or no elements
Sarsapirilla.fill();
```

### Methods

``` js
// use selector string to initialize on single element
Sarsapirilla.fill(document.querySelector('.sasp'));

// or parse string
Sarsapirilla.parse('[obj|car]');
```

### Wordpress Plugin

Zip Sarsapirilla folder and upload to dashboard

### Updating Images

Upload new images to unsplash folder and run scan

---

License: GPL

By [Nomanic](http://www.nomanic.biz/)
