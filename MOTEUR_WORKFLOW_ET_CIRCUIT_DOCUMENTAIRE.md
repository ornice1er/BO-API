# Moteur de workflow & circuit documentaire — référence de configuration

Document de référence pour la configuration des e-services dans le Back-Office.
Il décrit les entités, **les données collectées à la création de chaque objet**, les règles
appliquées par le moteur, et les points d'intégration avec le Portail national des services (PNS).

Dernière mise à jour : juillet 2026.

---

## 1. Vue d'ensemble

Une demande (`requetes`) porte à tout instant deux pointeurs :

| Colonne             | Rôle                                     |
|---------------------|------------------------------------------|
| `current_etape_id`  | Où en est la demande dans le parcours    |
| `current_status_id` | Ce que voit l'usager                     |

Le parcours n'est **pas** stocké dans l'étape : il est décrit par les **transitions**
(`workflow_transitions`), qui relient deux étapes pour une prestation donnée. Les étapes
(`etapes`) sont **globales et partagées** entre e-services ; c'est la transition qui donne
le sens du parcours, prestation par prestation.

```
Prestation ──< WorkflowTransition >── Etape (globale)
                     │
                     └── EtapeVisibilite (qui voit / qui agit)

EtapeDocumentProduit ──< DocumentCircuitEtape (ordre de visa) ──> DocumentActe
```

---

## 2. Données collectées à la création

### 2.1 Étape — `etapes` (globale, réutilisable par plusieurs e-services)

| Champ | Type | Obligatoire | Rôle | Portée |
|-------|------|-------------|------|--------|
| `name`           | string | **Oui** | Libellé, **unique** sur toute la table | Identité |
| `type`           | enum   | **Oui** *(formulaire)* | `depot`, `traitement`, `visite`, `commission`, `delivrance` | Identité |
| `sla_days`       | int    | Non | Délai de traitement (jours) | **Valeur par défaut**, surchargeable par prestation |
| `unite_admin_id` | int    | Non | Unité administrative responsable | **Valeur par défaut**, surchargeable par prestation |
| `can_associate`  | bool   | Non | Association de la demande à une **session** | **Valeur par défaut**, surchargeable par prestation |
| `need_meeting`   | bool   | Non | Création d'un rendez-vous requise | **Valeur par défaut**, surchargeable par prestation |

Une étape ne connaît ni son e-service, ni ce qui la précède ou la suit : c'est la **transition**
qui porte cette information. Les quatre champs comportementaux ci-dessus ne sont que des
**valeurs par défaut** — la valeur qui fait foi est résolue par prestation (§ 2.2).

> 🗑️ **Champs supprimés.** `is_terminal` (migration `2026_06_30_002`) : les étapes étant globales,
> une même étape peut être finale pour un e-service et intermédiaire pour un autre ; le drapeau
> était structurellement faux et provoquait des clôtures prématurées — la terminalité est
> désormais **déduite du graphe** (§ 3.3). `allow_partial_save` (migration `2026_06_30_003`) :
> saisi dans la configuration mais lu par aucun code.

### 2.2 Étape par prestation — `etape_prestations` *(nouveau)*

Pendant de `prestation_statuses` : contextualise une étape globale pour un e-service donné.
Un champ `null` signifie « hérite de la valeur portée par l'étape ».

| Champ            | Type | Rôle |
|------------------|------|------|
| `prestation_id`  | int  | E-service concerné — **unique** avec `etape_id` |
| `etape_id`       | int  | Étape contextualisée |
| `sla_days`       | int  | Délai propre à cet e-service |
| `unite_admin_id` | int  | Unité responsable propre à cet e-service |
| `can_associate`  | bool | Association à une session propre à cet e-service |
| `need_meeting`   | bool | RDV requis propre à cet e-service |

**Pourquoi.** Une même étape « Traitement » peut demander 5 jours sur un visa de contrat et 30 sur
un agrément ; une étape « Instruction » peut exiger une visite de site pour l'un et rien pour
l'autre. Tant que ces valeurs vivaient uniquement sur `etapes`, configurer un e-service écrasait
silencieusement les autres — le même vice que `is_terminal`, en moins visible.

**Résolution** — `EtapePrestation::resoudre(prestationId, etapeId)` : la valeur du pivot si elle
est renseignée, sinon celle de l'étape. Les lecteurs sont :

| Lecteur | Usage |
|---------|-------|
| `RequeteRepository::calculerSlaRestant()` | Jours restants |
| `RequeteRepository::getAgentEmail()` | Unité de l'étape **d'arrivée** → agents notifiés |
| `RequeteRepository::getUniteAdminEmail()` | Unité de l'étape **de départ** → e-mail de l'unité notifiée |
| `RequeteRepository::getOne()` | Écrase `current_etape` avec les valeurs effectives (le front lit toujours la bonne valeur, sans le savoir) |
| `WorkflowStateController` | Échéance et retard |
| `Prestation::getNeedMeetingAttribute()` | « Cet e-service comporte-t-il un RDV ? » |

Aucun autre code ne lit ces quatre champs directement sur `etapes` : les `unite_admin_id` que l'on
croise ailleurs appartiennent à d'autres tables (`agents`, `etape_visibilites`, `planning_slots`,
`reponses`, `document_circuit_etapes`, `start_points`) et n'ont rien à voir avec l'étape.

**Écran** : *Configurations → Étapes par prestation*. Seules les étapes réellement présentes dans le
graphe de la prestation sont proposées ; chaque ligne indique si elle est **héritée** ou
**contextualisée**, et peut être réinitialisée.

> Le backfill de la migration `2026_06_30_004` recopie les valeurs actuelles pour chaque couple
> (prestation, étape) du graphe : le comportement au moment du déploiement est reproduit à
> l'identique.

### 2.3 Transition — `workflow_transitions`

C'est **l'objet central** : c'est lui qui décrit le parcours d'un e-service.

| Champ              | Type   | Obligatoire | Rôle |
|--------------------|--------|-------------|------|
| `prestation_id`    | int    | **Oui**     | E-service concerné — rend la transition spécifique |
| `etape_from_id`    | int    | **Oui**     | Étape de départ |
| `etape_to_id`      | int    | Non         | Étape d'arrivée |
| `condition_type`   | enum   | **Oui**     | `auto`, `validation`, `rejet`, `complement`, `signature`, `cloture`, `paraphe`, `prevalidation`, `choix_sortie`, `correction`, `retour_correction` |
| `status_result_id` | int    | **Oui**     | Statut appliqué à la demande après la transition (vu par l'usager) |
| `order`            | int    | **Oui**     | Départage plusieurs transitions de même `condition_type` |
| `is_active`        | bool   | Non         | Transition désactivable sans suppression |
| `notify_agent`     | bool   | Non         | Notifie les agents habilités à l'étape d'arrivée |
| `can_act_pns`      | bool   | Non         | Notifie le PNS après la transition (§ 5.1) |
| `decision`         | string | Si `can_act_pns` | Clé de décision convenue avec le PNS |
| `observation`      | text   | Non         | Note de configuration, non fonctionnelle |

### 2.4 Visibilité — `etape_visibilites` (qui voit, qui agit)

| Champ                    | Type   | Obligatoire | Rôle |
|--------------------------|--------|-------------|------|
| `workflow_transition_id` | int    | **Oui**     | Transition concernée |
| `role_name`              | string | **Oui**     | Rôle habilité — unique par (transition, scope, unité) |
| `can_read`               | bool   | Non         | Le rôle voit la demande dans sa banette |
| `can_act`                | bool   | Non         | Le rôle peut déclencher la transition |
| `scope_type`             | enum   | **Oui**     | `requete` ou `document` |
| `unite_admin_id`         | int    | Non         | Restreint l'habilitation à une unité |
| `doc_produit_id`         | int    | Si `scope_type = document` | Document concerné |

> **Convention `etape_to`** : on habilite un rôle sur la transition **qui mène à** l'étape où il doit
> agir. Le rôle DGT habilité à parapher est donc rattaché à la transition dont `etape_to` est
> « Paraphe DGT ». `peutAgir()` et la banette s'appuient sur cette convention.

### 2.5 Document produit — `etape_document_produits`

| Champ                 | Type   | Obligatoire | Rôle |
|-----------------------|--------|-------------|------|
| `prestation_id`       | int    | **Oui**     | E-service concerné |
| `name`                | string | **Oui**     | Libellé du document |
| `slug`                | string | **Oui**     | Identifiant technique (proposé automatiquement depuis le libellé) |
| `type`                | enum   | **Oui**     | `lettre`, `decision`, `attestation`, `pv` |
| `numero_prefix`       | string | **Oui**     | Préfixe de numérotation de l'acte |
| `generate_from`       | enum   | **Oui**     | `system` (généré par le BO) ou `pns` (produit par le PNS) |
| `template_key`        | string | Si `generate_from = system` | Clé du modèle de document |
| `content`             | html   | Non         | Corps prédéfini, préchargé dans l'éditeur WYSIWYG |
| `etape_edition_id`    | int    | **Oui**     | Étape à laquelle le document est rédigé |
| `etape_delivrance_id` | int    | Non         | Étape à laquelle il est délivré |
| `allow_correction`    | bool   | Non         | Autorise un retour en correction dans le circuit |
| `order`               | int    | Non         | Ordre d'affichage |
| `decision`            | string | Non         | Clé de décision PNS associée au document |

### 2.6 Étape de circuit documentaire — `document_circuit_etapes`

Décrit l'ordre de visa d'un document : qui fait quoi, dans quel ordre, et ce que cela change.

| Champ                  | Type   | Obligatoire | Rôle |
|------------------------|--------|-------------|------|
| `doc_produit_id`       | int    | **Oui**     | Document concerné |
| `role_name`            | string | **Oui**     | Rôle qui exécute l'action |
| `unite_admin_id`       | int    | Non         | Restriction à une unité |
| `action_type`          | enum   | **Oui**     | `edition`, `paraphe`, `prevalidation`, `signature`, `correction` |
| `status_after`         | string | **Oui**     | Statut du **document** après l'action |
| `requete_status_after` | string | Non         | Statut de la **demande** après l'action |
| `is_blocking`          | bool   | Non         | Interdit la progression tant que l'action n'est pas faite |
| `order`                | int    | **Oui**     | Position dans le circuit |
| `can_act_pns`          | bool   | Non         | **Nouveau** — déclenche le PNS après l'action (§ 5.2) |
| `decision`             | string | Si `can_act_pns` | **Nouveau** — clé de décision transmise au PNS |

---

## 3. Règles appliquées par le moteur

### 3.1 Choix de la transition

`RequeteRepository::avancerWorkflow()` résout la transition ainsi :

1. **Si `transition_id` est fourni** (cas du traitement depuis le Back-Office, où l'agent a
   explicitement choisi une transition dans la liste) : la transition est chargée telle quelle,
   après vérification qu'elle appartient bien à la prestation, qu'elle part de l'étape courante
   et qu'elle est active.
2. **Sinon** (appel PNS, transition automatique) : recherche par
   `(prestation_id, etape_from_id = current_etape_id, condition_type, is_active)`,
   triée par `order`, première trouvée.

> Le passage explicite de `transition_id` corrige le cas où plusieurs transitions partagent le
> même `condition_type` depuis une même étape : sans lui, le moteur retombait sur la première
> par `order` et envoyait la demande à la mauvaise étape.

La transition retenue applique :
`current_etape_id ← etape_to_id` et `current_status_id ← status_result_id`.

### 3.2 Couplage circuit documentaire ⇄ transitions

Une action de circuit ne fait avancer la demande que si son `action_type` **correspond** à un
`condition_type` de transition partant de l'étape courante. Concrètement :

```
document_circuit_etapes.action_type  ==  workflow_transitions.condition_type
```

L'écran de configuration du circuit affiche désormais un **bandeau de guidage** :
vert si une transition correspondante existe pour l'étape d'édition du document, orange sinon,
avec la liste des `condition_type` réellement disponibles. Objectif : empêcher une configuration
qui bloquerait silencieusement la demande.

### 3.3 Terminalité d'une étape (déduite, pas déclarée)

```php
RequeteRepository::estEtapeTerminale(int $prestationId, int $etapeId): bool
```

Une étape est terminale **pour une prestation donnée** s'il n'existe aucune transition active
partant d'elle pour cette prestation. Aucune configuration à saisir, aucune migration de données.

Conséquences :
- `avancerWorkflow()` ne clôture la demande que si l'étape d'arrivée est terminale **au sens du
  graphe de cette prestation** ;
- `getOne()` expose l'attribut calculé `etape_terminale`, que le front utilise pour masquer le
  bouton « Traiter » sur une demande arrivée au bout de son parcours.

---

## 4. Statuts et clôture

- Le statut de la demande provient toujours de `status_result_id` de la transition franchie
  (ou de `requete_status_after` pour une action de circuit).
- `closed_at` est renseigné à la clôture : soit à l'arrivée sur une étape terminale, soit lors de
  la **clôture d'une session** (job `CloseProjectRequests`, file d'attente `database`).
- Les drapeaux `pris_en_charge` et `isFinished` ne sont **plus écrits** par le moteur.

---

## 5. Intégration PNS — sens Back-Office → PNS

Tous les appels passent par `PNSService::reply()`, qui poste sur
`{PNS_URI}/api/portal/event/uxp/rest` la charge utile :

```json
{
  "data":     "…",      // observations / contenu
  "message":  "…",      // libellé lisible
  "status":   true,
  "decision": "…",      // clé convenue avec le PNS
  "link":     "https://…/storage/documents/…pdf",
  "comment":  "…"
}
```

Les URL transmises passent par `Common::dedupeBaseUrl()`, qui supprime une base URL dupliquée
en tête de lien (source d'un ancien bug de liens invalides côté usager).

Il existe **trois déclencheurs** :

### 5.1 Depuis une transition de workflow
Si la transition franchie porte `can_act_pns`, le PNS est notifié avec sa `decision`, le
commentaire de l'agent (`data` et `comment`) et le lien éventuel.

### 5.2 Depuis une étape du circuit documentaire *(nouveau)*
Si l'étape de circuit exécutée porte `can_act_pns`, `traiterDocument()` notifie le PNS avec la
`decision` configurée et le `file_url` de l'acte.
**Cas d'usage principal :** sur une action `signature`, demander au PNS de produire / retourner
le document signé.

### 5.3 Depuis l'upload manuel d'un document *(nouveau)*
Dans l'éditeur de document, onglet **Upload**, l'agent peut cocher
« Partager ce document au Portail national (PNS) » et saisir une clé de décision et des
observations. `POST /api/document-actes/{acte}/upload` accepte alors :

| Champ          | Type    | Rôle |
|----------------|---------|------|
| `file`         | PDF     | Fichier (obligatoire, ≤ 10 Mo) |
| `share_to_pns` | bool    | Déclenche la notification PNS |
| `decision`     | string  | Clé de décision |
| `comment`      | string  | Observations jointes |

La réponse renvoie `partage_pns` (`true` / `false`).

> Dans les trois cas, l'appel PNS est **non bloquant** : un PNS injoignable n'annule ni la
> transition, ni l'action de circuit, ni l'upload. L'échec part en `Log::warning`.

---

## 6. Périmètre : ce que le BO ne gère pas

Le volet documentaire côté usager, ainsi que les intégrations **APIEx** et **ANIP**, sont pris en
charge par le **PNS**, pas par le Back-Office. Le BO expose des points d'entrée (`eservices-*`) et
notifie le PNS ; il ne pilote pas ces flux.

La logique `needCorrection` n'est plus utilisée par le moteur : au-delà des statuts, tout le flux
est piloté par les **transitions** et le **circuit documentaire**.

---

## 7. Migrations associées

| Migration | Objet |
|-----------|-------|
| `2026_06_10_001_make_template_key_nullable` | `template_key` requis seulement si `generate_from = system` |
| `2026_06_10_002_restore_legacy_columns` | Restauration de colonnes retirées prématurément |
| `2026_06_10_003_drop_legacy_prestation_columns` | Nettoyage des colonnes prestation/requête mortes |
| `2026_06_10_004_add_need_confirmation_to_agendas` | `need_confirmation`, `usager_comment` — retour RDV usager |
| `2026_06_10_005_drop_isfinished_from_requetes` | Suppression du drapeau `isFinished` |
| `2026_06_10_006_ensure_queue_tables` | Tables de file d'attente (driver `database`) |
| `2026_06_30_001_add_pns_trigger_to_document_circuit_etapes` | `can_act_pns`, `decision` sur le circuit documentaire |
| `2026_06_30_002_drop_is_terminal_from_etapes` | Suppression du drapeau `is_terminal` (terminalité déduite du graphe) |
| `2026_06_30_003_drop_allow_partial_save_from_etapes` | Suppression d'un champ saisi mais jamais lu |
| `2026_06_30_004_create_etape_prestations_table` | Contextualisation des étapes par prestation + backfill |

---

## 8. Documents liés

- [`API_ESERVICES_AVANCER_WORKFLOW.md`](./API_ESERVICES_AVANCER_WORKFLOW.md) — sens PNS → Back-Office.
- [`API_COMMISSION_DOCUMENTATION.md`](./API_COMMISSION_DOCUMENTATION.md) — API commission.
