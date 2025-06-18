# Exercice technique – Intégration **Posts & Commentaires**

Bienvenue ! Ce dépôt Symfony sert de base à l’évaluation d’un·e développeur·se **junior**. L’objectif est de démontrer :

* la bonne pratique de **Git** ;
* la consommation d’une **API** externe (*posts*) ;
* l’enrichissement et la fusion de données avec la **base de données** locale ;
* l’écriture de **tests** unitaires et fonctionnels ;
* (optionnel) la mise en œuvre d’un **évènement Symfony** + envoi d’e‑mail.

---

## 1. Pré‑requis

| Outil                          | Version conseillée |
| ------------------------------ | ------------------ |
| **Docker & Docker Compose**    | ≥ 24.x             |
| **Postman** (ou Insomnia)      | —                  |

Le repo embarque les services *PHP‑FPM*, *APACHE*, *MySQL* et *Mailpit* décrits dans `compose.yml`.

---

## 2. Setup rapide

Depuis la racine du projet :

```bash
# 1 – Démarrage des conteneurs
$ docker compose up -d

# 2 – Installation des dépendances backend (Composer)
$ docker compose exec server composer install

# 3 – Migration du schéma Doctrine
$ docker compose exec server php bin/console doctrine:migrations:migrate --no-interaction

# 4 – Chargement des données de démonstration (Foundry stories ou fixtures)
$ docker compose exec server php bin/console foundry:load-stories --no-interaction
```

Points d’accès :

* **API** : [http://localhost:8080](http://localhost:8080)
* **MySQL** : 127.0.0.1:3308 (`root / exercice_symfony` par défaut)

---

## 3. Backlog – Fonctionnalités à livrer

### 3.1 Enrichir l’entité **Author**

1. Ajouter les champs :

    * `avatarUrl` `string(255)` (nullable) – URL d’avatar (Validator : `@Assert\Url`).
    * `bio` `text` (nullable) – courte biographie (≤ 500 car.).
2. Générer la migration Doctrine et mettre à jour les fixtures.

### 3.2 Client **Posts** externe

* Créer `App\Client\PostApiClient` (HTTP Client Symfony) :

    * `findByAuthorIds(array $ids)` et `findByAuthorId(int $id)` retournent des **DTO**.
* Les appels doivent être **mockés** dans les tests via `MockHttpClient`.

### 3.3 Endpoints Auteur ↔ Posts

> (Si possible) **Sérialisation** : toutes les réponses doivent être construites à partir d’objets (DTO, entités ou Value Objects) puis **sérialisées** en JSON au moyen du composant **Serializer** de Symfony. Les contrôleurs ne doivent pas retourner de tableaux associatifs bruts.

| Endpoint            | Méthode  | Description                                  |
| ------------------- | -------- | -------------------------------------------- |
| `/api/authors`      | **GET**  | Collection d’auteurs + posts + commentaires  |
| `/api/authors/{id}` | **GET**  | Détail d’un auteur (posts + commentaires)    |
| `/api/authors`      | **POST** | Création d’un auteur (nom + nouveaux champs) |

### 3.4 Entité **Comment**

* Champs : `id`, `content` (text), `createdAt` (immutable), **relations** :

    * `author` → ManyToOne **Author**
    * `postId` (int) – identifiant du post externe.
* Inclure les commentaires de chaque auteur dans la réponse API.

### 3.5 Couverture & CI (bonus)

Ajouter un test pour l'API de récupération d'un Auteur permettant de valider le bon fonctionnement de l'API
Si possible : Mocker l'appel vers l'API des Posts

### 3.6 (Optionnel) Notification e‑mail via **Event Dispatcher**

1. **Installer** la librairie :

   ```bash
   docker compose exec server composer require symfony/mailer
   ```
2. **Configurer** votre transport dans `.env` (`MAILER_DSN=smtp://…`).
3. **Émettre** un `AuthorCreatedEvent` juste après la persistance d’un auteur.
4. **Écouter** l’évènement avec un `AuthorCreatedListener` qui envoie un mail simple (une seule phrase).

> Cette feature est facultative ; sa réalisation sera valorisée.

---

## 4. Génération de fausses données (Foundry)

Les factories se trouvent dans `src/Factory`.

```php
// AuthorFactory.php
protected function getDefaults(): array
{
    return [
        'name'      => self::faker()->name(),
        'avatarUrl' => self::faker()->imageUrl(128, 128, 'people'),
        'bio'       => self::faker()->realTextBetween(80, 140),
    ];
}

// CommentFactory.php
protected function getDefaults(): array
{
    return [
        'content'   => self::faker()->realTextBetween(40, 180),
        'createdAt' => self::faker()->dateTimeBetween('-1 month'),
        'author'    => AuthorFactory::random(),
        'postId'    => self::faker()->numberBetween(1, 100),
    ];
}
```

Recharge des fixtures :

```bash
docker compose exec server php bin/console doctrine:schema:drop --force
docker compose exec server php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec server php bin/console foundry:load-stories --no-interaction
```

---

## 5. Tests

```bash
docker compose exec server php bin/phpunit --testsuite=unit,functional --coverage-text
```

* **MockHttpClient** pour isoler les appels réseau.
* Tests fonctionnels basés sur **WebTestCase**.

---

## 6. Workflow Git

1. Créez une branche `feature/<slug>` pour chaque fonctionnalité.
2. Commits atomiques au format **Conventional Commits** : `feat:`, `fix:`, `test:` …
3. Ouvrez une *pull‑request* vers `main` détaillant :

    * contexte et objectif ;
    * choix techniques ;
    * **difficultés rencontrées** et la façon dont vous avez tenté de les résoudre (réussite ou non) ;
    * limites / pistes d’amélioration.
4. Rebase ou squash propre avant merge.
5. Merge de votre branche

---

## 7. Bonnes pratiques attendues

* **PSR‑12** & typage strict.
* Services `private` par défaut, injection par constructeur.
* Utilisation systématique du composant **Serializer** pour la sortie JSON ; pas de `json_encode` manuel ni de tableaux associatifs bruts exposés.
* Variables sensibles dans `.env.local` (exclu de Git).
* Gestion des erreurs : exceptions dédiées ou objets `Result`.
* Contrôleurs fins ; la logique métier vit dans des services.

---

## 8. Rétrospective demandée

Dans **chaque PR**, merci d’ajouter un paragraphe « Retour d’expérience » exposant :

* les obstacles ou difficultés rencontrés ;
* les pistes explorées pour les surmonter ;
* le résultat (succès, contournement, échec) et ce que vous auriez tenté avec plus de temps.

---

### Ressources utiles

* **Swagger** de l’API externe : [https://witty-tick-fredericlesueurs-aa3deee5.koyeb.app/api/docs](https://witty-tick-fredericlesueurs-aa3deee5.koyeb.app/api/docs)
* **MockHttpClient** : [https://symfony.com/doc/current/http\_client.html#testing](https://symfony.com/doc/current/http_client.html#testing)
* **Symfony Mailer** : [https://symfony.com/doc/current/mailer.html](https://symfony.com/doc/current/mailer.html)
* **Symfony Serializer** : [https://symfony.com/doc/current/serializer.html](https://symfony.com/doc/current/serializer.html)

Bonne implémentation ! 🤓
