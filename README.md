# opengerp

Benvenuto in **opengerp**, il cuore open source di un sistema ERP scritto in PHP (Slim Framework).  
Questa versione fornisce **infrastruttura, contratti e componenti comuni** per sviluppare applicazioni gestionali e moduli verticali.  

Il modello adottato è **open-core**:  
- il **core** (questo repository) è rilasciato come software libero;  
- i **moduli verticali** (es. funzioni specifiche per settori industriali) restano disponibili come estensioni commerciali.

---

## ✨ Caratteristiche principali

- **PHP 8.3+**, PSR-4 autoload, standard PSR-12.
- **Slim Framework 4** come micro-framework HTTP.
- **Dependency Injection** con PHP-DI.
- **Contratti e DTO** per aree chiave (fatturazione, anagrafiche, pagamenti…).
- **Implementazioni di base/No-Op** sempre disponibili.
- Sistema di **estensioni modulari**: i moduli possono registrare servizi, rotte e migrazioni.
- **Test unitari** con PHPUnit.
- **Licenza permissiva** (MIT/Apache-2.0).

---
