# Système de Commission - Documentation

## Vue d'ensemble

Ce système permet de gérer des commissions d'évaluation de dossiers/requêtes avec les fonctionnalités suivantes :

### Entités principales

1. **Members** - Les membres des commissions
2. **Commission** - Les commissions d'évaluation
3. **CommissionMembers** - Table de liaison entre membres et commissions
4. **CommissionRequete** - Table de liaison entre commissions et requêtes
5. **EtudeDossier** - Évaluations individuelles des dossiers par les membres

## Structure des modèles

### Member
- `lastname` - Nom de famille
- `firstname` - Prénom
- `email` - Email (unique)
- `fonction` - Fonction/Poste
- `is_active` - Statut actif/inactif

### Commission
- `name` - Nom de la commission
- `arrete_file` - Fichier d'arrêté
- `decret_file` - Fichier de décret
- `status` - Statut (active, closed, etc.)
- `responsable` - Responsable de la commission

### CommissionMembers (Table de liaison)
- `commission_id` - ID de la commission
- `member_id` - ID du membre
- `is_active` - Statut actif dans cette commission

### CommissionRequete (Table de liaison)
- `commission_id` - ID de la commission
- `requete_id` - ID de la requête
- `global_mark` - Note globale attribuée
- `status` - Statut de la requête dans cette commission

### EtudeDossier
- `commission_member_id` - ID de la relation commission-membre
- `commission_requete_id` - ID de la relation commission-requête
- `mark` - Note attribuée par le membre
- `status` - Statut de l'évaluation
- `comment` - Commentaire optionnel

## Fonctionnalités principales

### 1. Lister les commissions
```php
$commissions = Commission::all();
$activeCommissions = Commission::active()->get();
```

### 2. Lister les membres
```php
$members = Member::all();
$activeMembers = Member::where('is_active', true)->get();
```

### 3. Afficher les dossiers inscrits en commission
```php
$commission = Commission::find($id);
$requetes = $commission->requetes;
```

### 4. Afficher les membres sélectionnés pour la commission
```php
$commission = Commission::find($id);
$members = $commission->members()->wherePivot('is_active', true)->get();
```

### 5. Chaque membre peut noter chaque demande
```php
// Créer une évaluation
EtudeDossier::create([
    'commission_member_id' => $commissionMemberId,
    'commission_requete_id' => $commissionRequeteId,
    'mark' => 15.5,
    'status' => 'completed',
    'comment' => 'Dossier conforme'
]);
```

### 6. Le responsable peut consulter les notes et donner le statut final
```php
// Obtenir toutes les évaluations d'une requête dans une commission
$etudeDossiers = EtudeDossier::where('commission_requete_id', $commissionRequeteId)->get();

// Calculer la moyenne des notes
$averageMark = $etudeDossiers->avg('mark');

// Mettre à jour le statut final
$commissionRequete = CommissionRequete::find($commissionRequeteId);
$commissionRequete->update([
    'global_mark' => $averageMark,
    'status' => 'approved' // ou 'rejected'
]);
```

### 7. Le responsable peut clôturer la commission
```php
$commission = Commission::find($id);
$commission->update(['status' => 'closed']);
```

## Relations Eloquent

### Member
- `commissions()` - Commissions auxquelles appartient le membre
- `commissionMembers()` - Relations commission-membre
- `etudeDossiers()` - Évaluations effectuées par le membre

### Commission
- `members()` - Membres de la commission
- `commissionMembers()` - Relations commission-membre
- `requetes()` - Requêtes associées à la commission
- `commissionRequetes()` - Relations commission-requête
- `etudeDossiers()` - Évaluations de la commission

### CommissionMember
- `commission()` - Commission associée
- `member()` - Membre associé
- `etudeDossiers()` - Évaluations effectuées

### CommissionRequete
- `commission()` - Commission associée
- `requete()` - Requête associée
- `etudeDossiers()` - Évaluations de cette requête

### EtudeDossier
- `commissionMember()` - Membre qui a évalué
- `commissionRequete()` - Requête évaluée
- `member()` - Membre (via relation)
- `commission()` - Commission (via relation)
- `requete()` - Requête (via relation)

## Scopes utiles

### Commission
- `active()` - Commissions actives
- `closed()` - Commissions fermées

### CommissionRequete
- `pending()` - Requêtes en attente
- `approved()` - Requêtes approuvées
- `rejected()` - Requêtes rejetées

### EtudeDossier
- `pending()` - Évaluations en attente
- `completed()` - Évaluations terminées
- `withMark()` - Évaluations avec note

## Exemples d'utilisation

### Créer une commission avec des membres
```php
$commission = Commission::create([
    'name' => 'Commission d\'évaluation 2024',
    'responsable' => 'Jean Dupont',
    'status' => 'active'
]);

$commission->members()->attach($memberIds, ['is_active' => true]);
```

### Ajouter une requête à une commission
```php
$commission->requetes()->attach($requeteId, [
    'status' => 'pending'
]);
```

### Évaluer un dossier
```php
$etudeDossier = EtudeDossier::create([
    'commission_member_id' => $commissionMember->id,
    'commission_requete_id' => $commissionRequete->id,
    'mark' => 18.5,
    'status' => 'completed',
    'comment' => 'Dossier excellent, recommandé pour approbation'
]);
```

### Calculer la note moyenne d'une requête
```php
$averageMark = $commissionRequete->etudeDossiers()
    ->whereNotNull('mark')
    ->avg('mark');
```

## Migration

Pour créer les tables, exécutez les migrations dans l'ordre :

```bash
php artisan migrate
```

Les migrations créent automatiquement les contraintes de clés étrangères et les index nécessaires. 