@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../vendor/phpcr/phpcr-utils/bin/phpcr
php "%BIN_TARGET%" %*
