# API Documentation - Système de Commission

## Vue d'ensemble

Ce document décrit toutes les APIs disponibles pour le système de gestion des commissions d'évaluation de dossiers.

## Base URL
```
http://localhost:8000/api
```

## Authentification
Toutes les APIs nécessitent une authentification JWT. Incluez le token dans le header :
```
Authorization: Bearer {your_jwt_token}
```

## 1. Gestion des Membres (Members)

### 1.1 Lister tous les membres
```http
GET /api/members
```

**Paramètres de filtrage (optionnels) :**
- `lastname` - Filtrer par nom de famille
- `firstname` - Filtrer par prénom
- `email` - Filtrer par email
- `fonction` - Filtrer par fonction
- `is_active` - Filtrer par statut actif
- `per_page` - Nombre d'éléments par page

**Réponse :**
```json
{
    "success": true,
    "message": "Récupération de la liste des membres",
    "data": [
        {
            "id": 1,
            "lastname": "Dupont",
            "firstname": "Jean",
            "email": "jean.dupont@example.com",
            "fonction": "Expert",
            "is_active": true,
            "created_at": "2024-01-27T10:00:00.000000Z",
            "updated_at": "2024-01-27T10:00:00.000000Z"
        }
    ]
}
```

### 1.2 Obtenir un membre par ID
```http
GET /api/members/{id}
```

### 1.3 Créer un nouveau membre
```http
POST /api/members
```

**Body :**
```json
{
    "lastname": "Dupont",
    "firstname": "Jean",
    "email": "jean.dupont@example.com",
    "fonction": "Expert",
    "is_active": true
}
```

### 1.4 Mettre à jour un membre
```http
PUT /api/members/{id}
```

### 1.5 Supprimer un membre
```http
DELETE /api/members/{id}
```

### 1.6 Obtenir les membres actifs
```http
GET /api/members/active
```

### 1.7 Obtenir les commissions d'un membre
```http
GET /api/members/{id}/commissions
```

## 2. Gestion des Commissions

### 2.1 Lister toutes les commissions
```http
GET /api/commissions
```

**Paramètres de filtrage (optionnels) :**
- `name` - Filtrer par nom
- `responsable` - Filtrer par responsable
- `status` - Filtrer par statut (active, closed)
- `per_page` - Nombre d'éléments par page

### 2.2 Obtenir une commission par ID
```http
GET /api/commissions/{id}
```

### 2.3 Créer une nouvelle commission
```http
POST /api/commissions
```

**Body :**
```json
{
    "name": "Commission d'évaluation 2024",
    "arrete_file": "arrete.pdf",
    "decret_file": "decret.pdf",
    "status": "active",
    "responsable": "Jean Dupont"
}
```

### 2.4 Mettre à jour une commission
```http
PUT /api/commissions/{id}
```

### 2.5 Supprimer une commission
```http
DELETE /api/commissions/{id}
```

### 2.6 Obtenir les commissions actives
```http
GET /api/commissions/active
```

### 2.7 Obtenir les commissions fermées
```http
GET /api/commissions/closed
```

### 2.8 Obtenir les membres d'une commission
```http
GET /api/commissions/{id}/members
```

### 2.9 Obtenir les requêtes d'une commission
```http
GET /api/commissions/{id}/requetes
```

### 2.10 Ajouter des membres à une commission
```http
POST /api/commissions/{id}/members
```

**Body :**
```json
{
    "member_ids": [1, 2, 3],
    "is_active": true
}
```

### 2.11 Ajouter des requêtes à une commission
```http
POST /api/commissions/{id}/requetes
```

**Body :**
```json
{
    "requete_ids": [1, 2, 3],
    "status": "pending"
}
```

### 2.12 Clôturer une commission
```http
POST /api/commissions/{id}/close
```

## 3. Gestion des Études de Dossiers

### 3.1 Lister toutes les études de dossiers
```http
GET /api/etude-dossiers
```

**Paramètres de filtrage (optionnels) :**
- `status` - Filtrer par statut (pending, completed)
- `commission_id` - Filtrer par commission
- `member_id` - Filtrer par membre
- `per_page` - Nombre d'éléments par page

### 3.2 Obtenir une étude de dossier par ID
```http
GET /api/etude-dossiers/{id}
```

### 3.3 Créer une nouvelle étude de dossier
```http
POST /api/etude-dossiers
```

**Body :**
```json
{
    "commission_member_id": 1,
    "commission_requete_id": 1,
    "mark": 15.5,
    "status": "completed",
    "comment": "Dossier conforme"
}
```

### 3.4 Mettre à jour une étude de dossier
```http
PUT /api/etude-dossiers/{id}
```

### 3.5 Supprimer une étude de dossier
```http
DELETE /api/etude-dossiers/{id}
```

### 3.6 Obtenir les études en attente
```http
GET /api/etude-dossiers/pending
```

### 3.7 Obtenir les études terminées
```http
GET /api/etude-dossiers/completed
```

### 3.8 Obtenir les études par commission
```http
GET /api/etude-dossiers/commission/{commissionId}
```

### 3.9 Obtenir les études par membre
```http
GET /api/etude-dossiers/member/{memberId}
```

### 3.10 Obtenir les études par requête
```http
GET /api/etude-dossiers/requete/{requeteId}
```

### 3.11 Obtenir les statistiques
```http
GET /api/etude-dossiers/statistics?commission_id={commissionId}
```

**Réponse :**
```json
{
    "success": true,
    "message": "Statistiques des études de dossiers récupérées",
    "data": {
        "total": 50,
        "pending": 10,
        "completed": 40,
        "with_marks": 35,
        "average_mark": 15.8
    }
}
```

## 4. Fonctionnalités Principales

### 4.1 Lister les commissions
```http
GET /api/commissions
```

### 4.2 Lister les membres
```http
GET /api/members
```

### 4.3 Afficher les dossiers inscrits en commission
```http
GET /api/commissions/{id}/requetes
```

### 4.4 Afficher les membres sélectionnés pour la commission
```http
GET /api/commissions/{id}/members
```

### 4.5 Chaque membre peut noter chaque demande
```http
POST /api/etude-dossiers
```

### 4.6 Le responsable peut consulter les notes et donner le statut final
```http
GET /api/etude-dossiers/requete/{requeteId}
```

### 4.7 Le responsable peut clôturer la commission
```http
POST /api/commissions/{id}/close
```

## 5. Codes de Réponse

- `200` - Succès
- `201` - Créé avec succès
- `400` - Requête incorrecte
- `401` - Non autorisé
- `404` - Non trouvé
- `419` - Session expirée
- `500` - Erreur serveur

## 6. Exemples d'utilisation

### 6.1 Créer une commission avec des membres
```bash
# 1. Créer la commission
curl -X POST http://localhost:8000/api/commissions \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Commission d'évaluation 2024",
    "responsable": "Jean Dupont",
    "status": "active"
  }'

# 2. Ajouter des membres
curl -X POST http://localhost:8000/api/commissions/1/members \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "member_ids": [1, 2, 3],
    "is_active": true
  }'
```

### 6.2 Évaluer un dossier
```bash
curl -X POST http://localhost:8000/api/etude-dossiers \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "commission_member_id": 1,
    "commission_requete_id": 1,
    "mark": 18.5,
    "status": "completed",
    "comment": "Dossier excellent, recommandé pour approbation"
  }'
```

### 6.3 Consulter les évaluations d'une requête
```bash
curl -X GET http://localhost:8000/api/etude-dossiers/requete/1 \
  -H "Authorization: Bearer {token}"
```

## 7. Validation des données

### 7.1 Membres
- `lastname` : requis, string, max 255 caractères
- `firstname` : requis, string, max 255 caractères
- `email` : requis, email unique
- `fonction` : requis, string, max 255 caractères
- `is_active` : boolean

### 7.2 Commissions
- `name` : requis, string, max 255 caractères
- `arrete_file` : optionnel, string, max 255 caractères
- `decret_file` : optionnel, string, max 255 caractères
- `status` : string, valeurs : active, closed
- `responsable` : requis, string, max 255 caractères

### 7.3 Études de dossiers
- `commission_member_id` : requis, integer, existe dans commission_members
- `commission_requete_id` : requis, integer, existe dans commission_requetes
- `mark` : optionnel, numeric, min 0, max 20
- `status` : string, valeurs : pending, completed
- `comment` : optionnel, string, max 1000 caractères

## 8. Logs et Traçabilité

Toutes les actions sont automatiquement tracées avec :
- Nom de l'action
- Description détaillée
- Horodatage
- Utilisateur concerné

Les logs sont accessibles via le système de logging de Laravel. 