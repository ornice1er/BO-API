# Documentation des Request Classes - Système de Commission

## Vue d'ensemble

Ce document décrit toutes les Request classes créées pour la validation des données dans le système de commission.

## 1. Request Classes pour les Membres

### 1.1 StoreMemberRequest

**Fichier :** `app/Http/Requests/StoreMemberRequest.php`

**Utilisation :** Validation lors de la création d'un nouveau membre

**Règles de validation :**
```php
[
    'lastname' => 'required|string|max:255',
    'firstname' => 'required|string|max:255',
    'email' => 'required|email|unique:members,email',
    'fonction' => 'required|string|max:255',
    'is_active' => 'boolean'
]
```

**Messages d'erreur personnalisés :**
- Nom de famille requis
- Email unique requis
- Fonction requise
- Validation des types de données

### 1.2 UpdateMemberRequest

**Fichier :** `app/Http/Requests/UpdateMemberRequest.php`

**Utilisation :** Validation lors de la mise à jour d'un membre

**Règles de validation :**
```php
[
    'lastname' => 'sometimes|string|max:255',
    'firstname' => 'sometimes|string|max:255',
    'email' => [
        'sometimes',
        'email',
        Rule::unique('members', 'email')->ignore($memberId)
    ],
    'fonction' => 'sometimes|string|max:255',
    'is_active' => 'sometimes|boolean'
]
```

**Particularités :**
- Utilise `Rule::unique()->ignore()` pour permettre la mise à jour de l'email
- Champs optionnels avec `sometimes`

## 2. Request Classes pour les Commissions

### 2.1 StoreCommissionRequest

**Fichier :** `app/Http/Requests/StoreCommissionRequest.php`

**Utilisation :** Validation lors de la création d'une nouvelle commission

**Règles de validation :**
```php
[
    'name' => 'required|string|max:255',
    'arrete_file' => 'nullable|string|max:255',
    'decret_file' => 'nullable|string|max:255',
    'status' => 'string|in:active,closed',
    'responsable' => 'required|string|max:255'
]
```

**Messages d'erreur personnalisés :**
- Nom de commission requis
- Responsable requis
- Statut limité aux valeurs autorisées

### 2.2 UpdateCommissionRequest

**Fichier :** `app/Http/Requests/UpdateCommissionRequest.php`

**Utilisation :** Validation lors de la mise à jour d'une commission

**Règles de validation :**
```php
[
    'name' => 'sometimes|string|max:255',
    'arrete_file' => 'nullable|string|max:255',
    'decret_file' => 'nullable|string|max:255',
    'status' => 'sometimes|string|in:active,closed',
    'responsable' => 'sometimes|string|max:255'
]
```

### 2.3 AddMembersToCommissionRequest

**Fichier :** `app/Http/Requests/AddMembersToCommissionRequest.php`

**Utilisation :** Validation lors de l'ajout de membres à une commission

**Règles de validation :**
```php
[
    'member_ids' => 'required|array',
    'member_ids.*' => 'integer|exists:members,id',
    'is_active' => 'boolean'
]
```

**Particularités :**
- Validation d'un tableau d'IDs
- Vérification de l'existence des membres
- Validation de chaque élément du tableau

### 2.4 AddRequetesToCommissionRequest

**Fichier :** `app/Http/Requests/AddRequetesToCommissionRequest.php`

**Utilisation :** Validation lors de l'ajout de requêtes à une commission

**Règles de validation :**
```php
[
    'requete_ids' => 'required|array',
    'requete_ids.*' => 'integer|exists:requetes,id',
    'status' => 'string|in:pending,approved,rejected'
]
```

## 3. Request Classes pour les Études de Dossiers

### 3.1 StoreEtudeDossierRequest

**Fichier :** `app/Http/Requests/StoreEtudeDossierRequest.php`

**Utilisation :** Validation lors de la création d'une étude de dossier

**Règles de validation :**
```php
[
    'commission_member_id' => 'required|integer|exists:commission_members,id',
    'commission_requete_id' => 'required|integer|exists:commission_requetes,id',
    'mark' => 'nullable|numeric|min:0|max:20',
    'status' => 'string|in:pending,completed',
    'comment' => 'nullable|string|max:1000'
]
```

**Particularités :**
- Validation des clés étrangères
- Note entre 0 et 20
- Commentaire limité à 1000 caractères

### 3.2 UpdateEtudeDossierRequest

**Fichier :** `app/Http/Requests/UpdateEtudeDossierRequest.php`

**Utilisation :** Validation lors de la mise à jour d'une étude de dossier

**Règles de validation :**
```php
[
    'mark' => 'nullable|numeric|min:0|max:20',
    'status' => 'sometimes|string|in:pending,completed',
    'comment' => 'nullable|string|max:1000'
]
```

## 4. Utilisation dans les Contrôleurs

### 4.1 Exemple avec MemberController

```php
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;

public function store(StoreMemberRequest $request)
{
    $result = $this->memberRepository->makeStore($request->validated());
    return Common::successCreate('Membre créé avec succès', $result);
}

public function update(UpdateMemberRequest $request, $id)
{
    $result = $this->memberRepository->makeUpdate($id, $request->validated());
    return Common::success('Membre mis à jour avec succès', $result);
}
```

### 4.2 Exemple avec CommissionController

```php
use App\Http\Requests\StoreCommissionRequest;
use App\Http\Requests\UpdateCommissionRequest;
use App\Http\Requests\AddMembersToCommissionRequest;
use App\Http\Requests\AddRequetesToCommissionRequest;

public function store(StoreCommissionRequest $request)
{
    $result = $this->commissionRepository->makeStore($request->validated());
    return Common::successCreate('Commission créée avec succès', $result);
}

public function addMembers(AddMembersToCommissionRequest $request, $id)
{
    $validated = $request->validated();
    $result = $this->commissionRepository->addMembers(
        $id, 
        $validated['member_ids'], 
        $validated['is_active'] ?? true
    );
    return Common::success('Membres ajoutés avec succès', $result);
}
```

## 5. Avantages des Request Classes

### 5.1 Séparation des responsabilités
- La validation est séparée de la logique métier
- Code plus maintenable et testable

### 5.2 Réutilisabilité
- Les règles de validation peuvent être réutilisées
- Cohérence dans l'application

### 5.3 Messages d'erreur personnalisés
- Messages en français
- Attributs personnalisés pour une meilleure UX

### 5.4 Validation automatique
- Laravel valide automatiquement les données
- Gestion automatique des erreurs de validation

## 6. Exemples de réponses d'erreur

### 6.1 Erreur de validation - Email déjà utilisé
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": [
            "Cette adresse email est déjà utilisée."
        ]
    }
}
```

### 6.2 Erreur de validation - Note invalide
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "mark": [
            "La note ne peut pas dépasser 20."
        ]
    }
}
```

### 6.3 Erreur de validation - Membre inexistant
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "member_ids.0": [
            "Le membre sélectionné n'existe pas."
        ]
    }
}
```

## 7. Tests des Request Classes

### 7.1 Exemple de test pour StoreMemberRequest
```php
public function test_store_member_validation()
{
    $response = $this->postJson('/api/members', [
        'lastname' => '',
        'email' => 'invalid-email',
        'fonction' => ''
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['lastname', 'email', 'fonction']);
}
```

### 7.2 Exemple de test pour UpdateMemberRequest
```php
public function test_update_member_validation()
{
    $member = Member::factory()->create();
    
    $response = $this->putJson("/api/members/{$member->id}", [
        'email' => 'invalid-email'
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
}
```

## 8. Bonnes pratiques

### 8.1 Nommage des classes
- `Store{Model}Request` pour la création
- `Update{Model}Request` pour la mise à jour
- `Add{Action}Request` pour les actions spéciales

### 8.2 Organisation des règles
- Règles requises en premier
- Règles optionnelles ensuite
- Validation des relations en dernier

### 8.3 Messages d'erreur
- Messages en français
- Messages clairs et informatifs
- Attributs personnalisés pour une meilleure UX

### 8.4 Validation des relations
- Utiliser `exists` pour valider les clés étrangères
- Utiliser `Rule::unique()->ignore()` pour les mises à jour

## 9. Extension des Request Classes

### 9.1 Ajout de règles conditionnelles
```php
public function rules(): array
{
    $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:members,email'
    ];

    if ($this->isMethod('PUT')) {
        $rules['email'] = [
            'required',
            'email',
            Rule::unique('members', 'email')->ignore($this->route('id'))
        ];
    }

    return $rules;
}
```

### 9.2 Validation personnalisée
```php
public function withValidator($validator)
{
    $validator->after(function ($validator) {
        if ($this->input('mark') > 20) {
            $validator->errors()->add('mark', 'La note ne peut pas dépasser 20.');
        }
    });
}
```

Cette documentation couvre toutes les Request classes créées pour le système de commission, avec des exemples d'utilisation et des bonnes pratiques. 