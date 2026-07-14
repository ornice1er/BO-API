# API — Avancement du workflow depuis le PNS

**Endpoint :** `POST /api/eservices-avancer-workflow`

Permet au PNS de notifier le Back-Office afin de faire progresser une demande en cours vers l'étape suivante de son workflow.

---

## En-têtes (Headers)

| Header         | Obligatoire | Valeur attendue    |
|----------------|-------------|--------------------|
| `Content-Type` | Oui         | `application/json` |
| `Accept`       | Oui         | `application/json` |

> Aucun token `Bearer` requis — la route est publique, dans le même groupe que les autres routes `eservices-*`.

---

## Corps de la requête (Body JSON)

```json
{
  "code_demande":     "REQ-2025-00123",
  "condition_type":   "validation",
  "comment":          "Traitement validé côté PNS",
  "planning_slot_id": 12,
  "retour_rdv":       true
}
```

| Champ              | Type      | Obligatoire | Description                                                                 |
|--------------------|-----------|-------------|-----------------------------------------------------------------------------|
| `code_demande`     | `string`  | **Oui**     | Code unique de la requête à faire avancer                                    |
| `condition_type`   | `string`  | Non         | Type de transition à appliquer *(défaut : `validation`)*                     |
| `comment`          | `string`  | Non         | Commentaire journalisé dans l'historique de la requête                       |
| `planning_slot_id` | `integer` | Non         | Créneau de planning choisi par l'usager, rattaché à la requête               |
| `retour_rdv`       | `boolean` | Non         | Réponse de l'usager à une proposition de rendez-vous *(voir ci-dessous)*     |

### Réponse de l'usager à un rendez-vous (`retour_rdv`)

Lorsque le Back-Office propose un rendez-vous, l'entrée d'agenda correspondante est créée avec
`need_confirmation = true` et sans réponse usager. Si `retour_rdv` est présent dans l'appel,
le Back-Office met à jour **le dernier rendez-vous encore en attente de confirmation** :

| Colonne de `agendas` | Valeur écrite                                                    |
|----------------------|------------------------------------------------------------------|
| `usager_response`    | `true` / `false` selon `retour_rdv`                              |
| `usager_comment`     | Le `comment` envoyé (affiché dans le détail de la demande)       |
| `status`             | `Confirmé par l'usager` ou `Décliné par l'usager`                |

> Ce traitement est **non bloquant** : s'il échoue (aucun rendez-vous en attente, par exemple),
> l'avancement du workflow reste effectif et l'incident est seulement journalisé.

### Valeurs acceptées pour `condition_type`

| Valeur          | Signification                             |
|-----------------|-------------------------------------------|
| `validation`    | Prise en charge / approbation *(défaut)*  |
| `prevalidation` | Pré-validation                            |
| `paraphe`       | Paraphe                                   |
| `signature`     | Signature                                 |
| `rejet`         | Rejet                                     |
| `complement`    | Demande de complément                     |
| `cloture`       | Clôture définitive                        |
| `choix_sortie`  | Choix de la voie de sortie                |
| `correction`    | Retour pour correction (métier)           |
| `retour_correction` | Retour pour correction (requérant)    |
| `auto`          | Transition automatique (sans action humaine) |

> La transition effectivement appliquée est la première transition **active** partant de l'étape
> courante pour cette prestation avec ce `condition_type`, triée par `order`.

---

## Réponses

### Succès — `200 OK`

```json
{
  "status": true,
  "message": "Workflow avancé avec succès",
  "data": {
    "id": 42,
    "code": "REQ-2025-00123",
    "current_etape_id": 5,
    "current_status_id": 3
  }
}
```

### Erreurs

| `status` | `message`                                                  | Cause                                                                 |
|----------|------------------------------------------------------------|-----------------------------------------------------------------------|
| `false`  | `"Données invalides : ..."`                                | `code_demande` absent ou mal formé                                    |
| `false`  | `"Demande introuvable"`                                    | Aucune requête ne correspond au `code_demande` fourni                 |
| `false`  | `"No query results for model [WorkflowTransition]"`        | Aucune transition configurée pour l'état actuel + `condition_type`    |

> **Convention :** toutes les réponses retournent un code HTTP `200`. Le résultat réel est indiqué par le champ `status` (`true` = succès, `false` = échec).

---

## Exemple cURL

```bash
curl -X POST https://votre-api/api/eservices-avancer-workflow \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "code_demande": "REQ-2025-00123",
    "condition_type": "validation",
    "comment": "Validé par le PNS"
  }'
```

---

## Notes d'intégration

- Le `code_demande` correspond au champ `code` de la table `requetes`.
- Si `condition_type` est omis, la transition `validation` est appliquée par défaut.
- Si la transition demandée n'existe pas dans le workflow configuré pour la prestation concernée, l'API retourne une erreur sans modifier l'état de la requête.
- Chaque appel est journalisé dans les logs applicatifs (table des traces).
