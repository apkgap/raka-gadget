# FTP Deployment Guidelines (lftp + Plesk)

## 1. Syntax Standard for `lftp`
- **Do not pass host arguments together with `-f`**:
  ```bash
  # ❌ INCORRECT (Triggers: open: invalid option -- 'f')
  lftp -u "$FTP_USERNAME","$FTP_PASSWORD" "$FTP_SERVER" -f "$LFTP_COMMANDS"

  # ✅ CORRECT PATTERN
  # Write the `open` command directly inside the script:
  {
    echo "open -u \"$FTP_USERNAME\",\"$FTP_PASSWORD\" \"$FTP_SERVER\""
    echo 'set cmd:fail-exit yes;'
    echo 'set ftp:ssl-allow yes;'
    echo 'set ssl:verify-certificate no;'
    echo 'set ftp:passive-mode yes;'
    echo 'set ftp:use-epsv no;'
    # mirror commands here
    echo 'quit'
  } > "$LFTP_COMMANDS"

  lftp -f "$LFTP_COMMANDS"
  ```

## 2. Plesk Chroot Path Normalization
- On Plesk and cPanel FTP setups, the user root directory is already chrooted.
- Absolute paths starting with a leading slash `/` fail with `550 CWD failed` or `Access denied`.
- Always normalize target directory strings in Bash:
  ```bash
  THEME_RAW="${THEME_RAW#/}"
  THEME_RAW="${THEME_RAW%/}"
  ```

## 3. Fail-Fast Execution
- Always enable `set cmd:fail-exit yes;` in `lftp` scripts so any transfer error immediately stops the CI/CD job with exit code 1.

## 4. IP / DNS Verification
- When configuring `FTP_SERVER`, verify that the IP resolves to the active web node IP matching the domain's A record (e.g. `103.27.200.76`), not a deprecated cluster management IP.
