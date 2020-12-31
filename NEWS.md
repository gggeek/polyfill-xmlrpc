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