del /q "C:\MAMP\htdocs\*"
for /d %%p in (C:\MAMP\htdocs\*) Do rd /Q /S "%%p"
//cd "C:\Users\bankb_000\Desktop\internship repo"
xcopy /s /q /y "C:\Users\bankb_000\Desktop\internship repo" "C:\MAMP\htdocs"