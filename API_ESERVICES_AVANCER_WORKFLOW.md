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
  "code_demande":   "REQ-2025-00123",
  "condition_type": "validation",
  "comment":        "Traitement validé côté PNS"
}
```

| Champ            | Type     | Obligatoire | Description                                                    |
|------------------|----------|-------------|----------------------------------------------------------------|
| `code_demande`   | `string` | **Oui**     | Code unique de la requête à faire avancer                      |
| `condition_type` | `string` | Non         | Type de transition à appliquer *(défaut : `validation`)*       |
| `comment`        | `string` | Non         | Commentaire journalisé dans l'historique de la requête         |

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
| `auto`          | Transition automatique (sans action humaine) |

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
