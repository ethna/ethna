<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset={$client_enc}">
    </head>
    <body>
        <h1>{$project_id}</h1>
        {'template i18n'|i18n}
        {'template i18n modifier'|upper|i18n}
        {'template i18n multiple modifier'|lower|upper|i18n}
    </body>
</html>
