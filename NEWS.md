## vXX (unreleased)

- Improved: added CI testing on php 8.4. Default the local testing container to using PHP 8.1 on Ubuntu Jammy


## v1.0 (2024/4/15)

- Improved: fixed many corner cases for function `xmlrpc_decode`

- Improved: moved CI testing from Travis to GitHub Actions. Added testing on php 8.1, 8.2 and 8.3. Default the local
  testing container to using PHP 7.4 on Ubuntu Focal

- Changed: bumped requirements to php 5.4, phpxmlrpc 4.10


## v1.0-RC1 (2022/1/30)

- Improved: servers now support answering calls to the `system.describeMethods` introspection method

- Improved: composer.json now declares this package as replacement for `ext-xmlrpc`


## v1.0-beta (2021/1/11)

- Improved: support for handling UTF8 characters both in received and in generated xml
- Improved: support for the `$encoding` argument in `xmlrpc_decode()` and `xmlrpc_decode_request()`
- Improved: partial support for the `$options` parameter in `xmlrpc_encode_request`, allowing UTF8 in native strings
  via setting `'encoding' => 'UTF-8'` and `'escaping' => 'markup'`


## v1.0-alpha (2020/12/31)

Hello world!

The initial release of this lib comes at the very end of a difficult year. Let's hope next year will be better for everybody!

All details about the status of the implementation can be found in the source code in file Xmlrpc.php.

A high level overview is: everything is broadly working except for bugs and for the following missing features:
- character set handling: at the moment only Latin1 (aka iso-8859-1) is supported - the `$encoding` argument does nothing
  in `xmlrpc_decode()` and `xmlrpc_decode_request()`
- the `$output_options` argument in `xmlrpc_encode_request()` does nothing
- the `xmlrpc_parse_method_descriptions` and `xmlrpc_server_register_introspection_callback` functions exist but do nothing
- xmlrpc server method `system.describeMethods` is not implemented
