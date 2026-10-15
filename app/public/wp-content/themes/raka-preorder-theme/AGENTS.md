# Pre-order Theme Guidelines & Deployment Rules

## 1. Scope and Responsibilities
- Strictly preserve presentation boundaries in `single-preorder.php` and `template-parts/`.
- Maintain ID before class styling conventions.

## 2. CI/CD & Fast FTP Deployment Pattern (lftp + Plesk)
Whenever deploying this theme or its companion plugins (`preorder-raka`, `my-wc-passkeys`) via GitHub Actions:
- **`lftp` Script Calling Pattern**:
  - Always embed the `open -u "user","pass" "host"` command inside the script file rather than on the CLI after host/user arguments.
  - Run via: `lftp -f "$LFTP_COMMANDS"`.
  - Always include `set cmd:fail-exit yes;`.
- **Path Normalization (Plesk Chroot)**:
  - Do not use leading slashes on target directories (e.g. use `raka-preorder...` not `/raka-preorder...`).
  - Strip leading/trailing slashes in the deploy step.
- **Server DNS / IP**:
  - Primary host must resolve to the live domain node IP (`103.27.200.76`).
