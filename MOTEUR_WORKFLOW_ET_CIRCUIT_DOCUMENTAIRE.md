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
qui porte cette information.

> ✏️ **Les quatre champs comportementaux ne se saisissent plus sur l'étape globale.** Le formulaire
> de l'étape ne collecte plus que `name` et `type` ; le délai, l'unité, le RDV et l'association à
> une session se définissent **par prestation** (§ 2.2), pour être précis à chaque e-service. Les
> colonnes restent en base comme **valeur de repli** (résolution), et portent les valeurs de
> l'existant reprises par la recomposition — mais elles ne sont plus éditées globalement.

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

#### Mise en ligne sur un existant en production

**Aucune étape n'est dupliquée, aucun `etape_id` n'est réaffecté.** `etape_prestations` est une
table de *surcharge*, pas de remplacement : les 11 colonnes qui référencent une étape
(`requetes.current_etape_id`, `requete_etape_logs`, `workflow_transitions`,
`etape_document_produits`, `etape_documents`, `motifs_rejet`, `requete_files`, `workflows`)
continuent de pointer sur les mêmes lignes. **Les demandes déjà en circulation ne changent ni
d'étape, ni de statut, ni de parcours.** C'est la raison même pour laquelle le pivot a été préféré
à un dédoublement des étapes par e-service, qui aurait exigé de remapper tout l'existant.

Le backfill de la migration ne lit toutefois que `workflow_transitions`. Or un couple
(prestation, étape) peut exister ailleurs — et notamment **sur une demande en cours posée sur une
étape retirée du graphe depuis son dépôt**. D'où l'étape de recomposition :

```bash
php artisan etapes:recomposer              # simulation : liste les couples manquants
php artisan etapes:recomposer --appliquer  # crée les lignes, valeurs recopiées à l'identique
```

La commande recense les couples appartenant au **parcours réel** de chaque prestation —
transitions, workflow legacy, documents produits, pièces justificatives, motifs de rejet et
**demandes en cours**. L'**historique** (`requete_etape_logs`) est volontairement **exclu** : une
étape seulement franchie par de vieilles demandes n'est pas forcément une étape du parcours actuel,
et créait des couples parasites.

Pour chaque couple retenu, elle **lie l'étape à la prestation et (re)synchronise les quatre champs
comportementaux** depuis l'étape globale — qu'il s'agisse d'une ligne manquante (création) ou d'une
ligne déjà présente mais désalignée (resynchronisation). Les lignes **orphelines** (présentes en
base mais hors parcours) sont **signalées, jamais supprimées automatiquement**.

> ⚠️ La resynchronisation **écrase** toute valeur par prestation avec la valeur globale de l'étape.
> C'est l'effet voulu **au moment de la recomposition** (avant toute calibration manuelle). Ne pas
> relancer après avoir surchargé des étapes à la main, sous peine de perdre ces surcharges.

Elle se termine par un **garde-fou** : pour chaque demande en cours, elle compare la valeur résolue
à celle que l'ancien code lisait, et échoue si un seul écart apparaît.

Les étapes encore référencées mais absentes du graphe actuel restent configurables : l'écran les
affiche avec un badge **« Hors graphe »**.

Ordre de déploiement :

1. `php artisan migrate` (dont `2026_06_30_004`)
2. `php artisan etapes:recomposer` puis `--appliquer`
3. `php artisan etapes:audit-partage` → arbitrage métier (ci-dessous)

#### Recalibrer les configurations existantes

Le backfill **préserve** l'existant, il ne le corrige pas — c'est voulu : un déploiement qui change
silencieusement des délais en production serait pire que le défaut qu'il répare. La recalibration
est donc un geste **volontaire**, à faire après coup, et elle ne concerne qu'une partie des étapes.

**Une étape utilisée par un seul e-service n'a rien à recalibrer** : sa valeur ne pouvait être
écrasée par personne, le backfill est exact par construction. Seules les **étapes partagées** par
plusieurs e-services demandent un arbitrage : elles portent aujourd'hui la même valeur partout,
parce que c'est tout ce que l'ancien modèle savait exprimer.

Procédure :

1. **Auditer** — sur le serveur, après migration :
   ```bash
   php artisan etapes:audit-partage        # --tout pour inclure les étapes exclusives
   ```
   La commande sépare les étapes exclusives (rien à faire) des étapes partagées, et affiche pour
   chacune ses valeurs effectives, e-service par e-service.

2. **Arbitrer** — pour chaque étape partagée, la question est métier, pas technique :
   *ce délai / cette unité / ce RDV doit-il vraiment être le même pour ces deux e-services ?*
   Si oui, il n'y a rien à faire : l'héritage exprime déjà l'intention.

3. **Surcharger** — là où la réponse est non : *Configurations → Étapes par prestation*, choisir
   l'e-service, cliquer sur l'étape, saisir la valeur propre à cet e-service. Les autres e-services
   qui partagent l'étape ne bougent pas.

L'écran signale lui-même les étapes concernées par un bandeau et un badge
**« Partagée (n) »** — l'administrateur voit donc immédiatement les seules lignes qui méritent son
attention, sans avoir à relancer l'audit.

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
| `2026_07_15_001_add_stage_structure_and_document_destinataire` | `requetes.structure_id` + `etape_document_produits.destinataire` |
| `2026_07_15_002_add_rapport_stage_and_auto_delivery_link` | Rapport de stage + liaison de délivrance automatique |

---

## 8. Stage : structure d'accueil, documents à un tiers & délivrance automatique

Cas d'usage : **PS00928** (demande de stage) et **PS00926** (attestation, délivrée à la suite).

### 8.1 Structure d'affectation

Une demande peut être rattachée à une **structure d'accueil** — une unité administrative
existante (`requetes.structure_id → unite_admins`). L'agent la renseigne au traitement
(section « Structure d'affectation » du détail, `PUT /requetes/{id}/structure`).

Les variables `{{structure_nom}}`, `{{structure_sigle}}`, `{{structure_email}}` deviennent
disponibles dans les documents produits (`extraireVariables`), pour qu'un document s'adresse à
la structure.

### 8.2 Destinataire d'un document produit

`etape_document_produits.destinataire` ∈ { `usager`, `structure` } :

- `usager` *(défaut)* — délivrance au demandeur via le PNS (comportement historique) ;
- `structure` — le PDF est **envoyé par e-mail** à `structure.email` **quand la demande atteint
  son étape terminale FAVORABLE** (`RequeteRepository::envoyerDocumentsStructure`, via
  `Mailer::sendSimpleWithFile`, template `emails/document_structure`). Non bloquant : e-mail
  manquant ou envoi en échec → journalisé, le workflow n'est pas interrompu.

Ex. : l'**autorisation de stage** = `destinataire = structure` ; la **lettre d'acceptation** =
`destinataire = usager`.

### 8.3 Rapport de stage

Un **rapport de stage** peut être déposé par un **agent** sur une demande de stage (même
clôturée) : section « Rapport de stage » du détail, `POST/DELETE /requetes/{id}/rapport-stage`,
stocké dans `requetes.rapport_stage_path`. La section n'apparaît que si la prestation est
**source** d'une prestation à délivrance automatique (`getOne` → `manages_rapport_stage`).

### 8.4 Délivrance automatique (PS00926 à la suite de PS00928)

Configuration sur la prestation dépendante (PS00926) :

| Champ | Rôle |
|-------|------|
| `is_automatic_delivered` | Active la délivrance automatique |
| `source_prestation_id` | Prestation prérequise (PS00928) |
| `reference_field_key` | Clé du champ de `step_contents` où le demandeur fournit la référence de la demande source |

À la **soumission** d'une demande d'une prestation `is_automatic_delivered`,
`RequeteRepository::tenterDelivranceAutomatique` s'exécute (après commit, non bloquant) et
applique une **validation stricte** :

1. la référence de la demande source est présente dans le formulaire ;
2. cette demande source existe, appartient à `source_prestation_id`, au **même demandeur**, et est
   **aboutie favorablement** (`isTreated` && !`isDeclined` && `closed_at`) ;
3. cette demande source porte un **rapport de stage** (`rapport_stage_path`).

Si tout est réuni, le workflow est **auto-avancé** le long des transitions favorables
(`auto`, `validation`, `prevalidation`, `paraphe`, `signature`, plafonné à 15 étapes) jusqu'à
l'étape terminale — ce qui déclenche la **délivrance PNS** existante (configurer `can_act_pns` +
`decision` sur la transition terminale du 926). Sinon, la demande reste en **traitement manuel**,
la raison étant journalisée. Le BO ne génère pas l'attestation : sa production reste au PNS.

---

## 9. Documents liés

- [`API_ESERVICES_AVANCER_WORKFLOW.md`](./API_ESERVICES_AVANCER_WORKFLOW.md) — sens PNS → Back-Office.
- [`API_COMMISSION_DOCUMENTATION.md`](./API_COMMISSION_DOCUMENTATION.md) — API commission.
