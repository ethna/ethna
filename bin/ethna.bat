@echo off

rem
rem   ethna.bat
rem
rem   simple command line gateway
rem
rem   $Id$
rem

if "%OS%"=="Windows_NT" @setlocal

if NOT "%PHP_PEAR_INSTALL_DIR%" == "" (
set DEFAULT_ETHNA_HOME=%PHP_PEAR_INSTALL_DIR%\Ethna
) ELSE (
set DEFAULT_ETHNA_HOME=%~dp0
)

goto init
goto cleanup

:init

if "%ETHNA_HOME%" == "" set ETHNA_HOME=%DEFAULT_ETHNA_HOME%
set DEFAULT_ETHNA_HOME=

if "%PHP_COMMAND%" == "" goto no_phpcommand
if "%PHP_CLASSPATH%" == "" goto set_classpath

goto run
goto cleanup

:run
IF EXIST "@PEAR-DIR@" (
  %PHP_COMMAND% -d html_errors=off -qC "@PEAR-DIR@\Ethna\ethna_handle.php" %1 %2 %3 %4 %5 %6 %7 %8 %9
) ELSE (
  %PHP_COMMAND% -d html_errors=off -qC "%ETHNA_HOME%\bin\ethna_handle.php" %1 %2 %3 %4 %5 %6 %7 %8 %9
)
goto cleanup

:no_phpcommand
set PHP_COMMAND=php.exe
goto init

:set_classpath
set PHP_CLASSPATH=%ETHNA_HOME%\class
goto init

:cleanup
if "%OS%"=="Windows_NT" @endlocal
REM pause
