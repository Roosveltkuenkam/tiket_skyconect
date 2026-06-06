<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SettingManager
{
    public static function definitions()
    {
        return [
            'general' => [
                'label' => 'General',
                'settings' => [
                    'platform.name' => ['label' => 'Nom plateforme', 'type' => 'text', 'default' => 'SkyConnect'],
                    'platform.logo_path' => ['label' => 'Logo actuel', 'type' => 'file', 'default' => 'images/logo-skyconnect.PNG'],
                    'platform.currency' => ['label' => 'Devise', 'type' => 'text', 'default' => 'XAF'],
                    'platform.country' => ['label' => 'Pays', 'type' => 'text', 'default' => 'Cameroun'],
                ],
            ],
            'sales' => [
                'label' => 'Vente',
                'settings' => [
                    'sales.low_stock_threshold' => ['label' => 'Seuil stock faible par defaut', 'type' => 'number', 'default' => 10],
                    'sales.order_expiration_minutes' => ['label' => 'Expiration commande (minutes)', 'type' => 'number', 'default' => 15],
                    'sales.platform_fee_percent' => ['label' => 'Frais plateforme (%)', 'type' => 'number', 'default' => 0],
                ],
            ],
            'campay' => [
                'label' => 'Campay',
                'settings' => [
                    'campay.username' => ['label' => 'Campay username', 'type' => 'text', 'default' => null],
                    'campay.password' => ['label' => 'Campay password', 'type' => 'password', 'default' => null, 'secret' => true],
                    'campay.app_key' => ['label' => 'Campay app key', 'type' => 'password', 'default' => null, 'secret' => true],
                    'campay.base_url' => ['label' => 'Campay base URL', 'type' => 'text', 'default' => null],
                ],
            ],
            'mail' => [
                'label' => 'Mail SMTP',
                'settings' => [
                    'mail.host' => ['label' => 'SMTP host', 'type' => 'text', 'default' => null],
                    'mail.port' => ['label' => 'SMTP port', 'type' => 'number', 'default' => 587],
                    'mail.encryption' => ['label' => 'SMTP encryption', 'type' => 'text', 'default' => 'tls'],
                    'mail.username' => ['label' => 'SMTP username', 'type' => 'text', 'default' => null],
                    'mail.password' => ['label' => 'SMTP password', 'type' => 'password', 'default' => null, 'secret' => true],
                    'mail.from_address' => ['label' => 'Email expediteur', 'type' => 'text', 'default' => 'hello@skyconnect.local'],
                    'mail.from_name' => ['label' => 'Nom expediteur', 'type' => 'text', 'default' => 'SkyConnect'],
                ],
            ],
            'legal' => [
                'label' => 'Legal',
                'settings' => [
                    'legal.terms' => ['label' => 'Conditions generales', 'type' => 'textarea', 'default' => self::defaultTerms()],
                    'legal.privacy' => ['label' => 'Politique de confidentialite', 'type' => 'textarea', 'default' => self::defaultPrivacy()],
                ],
            ],
        ];
    }

    public static function ensureDefaults()
    {
        foreach (self::definitions() as $group => $definitionGroup) {
            foreach ($definitionGroup['settings'] as $key => $definition) {
                Setting::firstOrCreate(
                    ['key' => $key],
                    [
                        'value' => self::encodeValue($definition['default'] ?? null, $definition['secret'] ?? false),
                        'type' => $definition['type'],
                        'setting_group' => $group,
                        'is_secret' => $definition['secret'] ?? false,
                    ]
                );
            }
        }
    }

    public static function get($key, $default = null)
    {
        try {
            if (! Schema::hasTable('settings')) {
                return $default;
            }

            $setting = Setting::where('key', $key)->first();
        } catch (Throwable $exception) {
            return $default;
        }

        if (! $setting) {
            return $default;
        }

        return self::decodeValue($setting->value, $setting->is_secret) ?? $default;
    }

    public static function set($key, $value, array $definition)
    {
        $isSecret = $definition['secret'] ?? false;

        return Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => self::encodeValue($value, $isSecret),
                'type' => $definition['type'],
                'setting_group' => self::groupFor($key),
                'is_secret' => $isSecret,
            ]
        );
    }

    public static function displayValue(Setting $setting)
    {
        if ($setting->is_secret) {
            return $setting->value ? '********' : '';
        }

        return self::decodeValue($setting->value, false);
    }

    public static function applyRuntimeConfig()
    {
        if (! self::tableReady()) {
            return;
        }

        config([
            'app.name' => self::get('platform.name', config('app.name')),
            'mail.mailers.smtp.host' => self::get('mail.host', config('mail.mailers.smtp.host')),
            'mail.mailers.smtp.port' => self::get('mail.port', config('mail.mailers.smtp.port')),
            'mail.mailers.smtp.encryption' => self::get('mail.encryption', config('mail.mailers.smtp.encryption')),
            'mail.mailers.smtp.username' => self::get('mail.username', config('mail.mailers.smtp.username')),
            'mail.mailers.smtp.password' => self::get('mail.password', config('mail.mailers.smtp.password')),
            'mail.from.address' => self::get('mail.from_address', config('mail.from.address')),
            'mail.from.name' => self::get('mail.from_name', config('mail.from.name')),
            'services.campay.username' => self::get('campay.username'),
            'services.campay.password' => self::get('campay.password'),
            'services.campay.app_key' => self::get('campay.app_key'),
            'services.campay.base_url' => self::get('campay.base_url'),
        ]);
    }

    public static function defaultTerms()
    {
        return <<<'TEXT'
Conditions generales d'utilisation de SkyConnect

Date de derniere mise a jour : 06 juin 2026

1. Objet

Les presentes conditions generales encadrent l'utilisation de la plateforme SkyConnect. SkyConnect permet aux proprietaires de reseaux Wi-Fi, hotels, residences, commerces, campus et autres points d'acces de creer, vendre et suivre des tickets Internet prepayes.

En utilisant SkyConnect, l'utilisateur accepte ces conditions et s'engage a respecter les regles de securite, de paiement, de confidentialite et d'utilisation de la plateforme.

2. Definitions

Plateforme : l'application SkyConnect, ses interfaces web, tableaux de bord, services, outils, modules de paiement, pages publiques et espaces d'administration.

Client proprietaire : personne ou entreprise qui utilise SkyConnect pour gerer ses routeurs, forfaits, tickets, ventes et paiements.

Utilisateur final : personne qui achete ou utilise un ticket Wi-Fi vendu via SkyConnect.

Administrateur : membre autorise de l'equipe SkyConnect ou d'un compte client pouvant acceder a certaines fonctions de gestion.

Ticket Wi-Fi : code, identifiant ou bon d'acces permettant de se connecter a un reseau Wi-Fi pendant une duree ou selon un volume defini.

3. Acces au service

L'acces a SkyConnect peut necessiter la creation d'un compte. L'utilisateur s'engage a fournir des informations exactes, completes et a jour, notamment son nom, son email, son telephone, son entreprise, sa ville et son pays.

SkyConnect se reserve le droit de refuser, suspendre ou supprimer un compte si les informations fournies sont fausses, incompletes, frauduleuses, offensantes ou susceptibles de porter atteinte a l'entreprise, a ses clients ou a la securite de la plateforme.

4. Protection des interets de SkyConnect

SkyConnect conserve tous les droits necessaires pour proteger son activite, son image, ses revenus, sa securite technique et les interets de ses partenaires.

A ce titre, SkyConnect peut notamment :

- suspendre un compte en cas de fraude, abus, tentative de piratage, impaye ou violation des presentes conditions ;
- limiter l'acces a certaines fonctionnalites si un compte presente un risque technique, financier ou juridique ;
- bloquer une operation suspecte, un paiement anormal, un remboursement abusif ou une tentative d'acces non autorisee ;
- conserver les preuves techniques necessaires a la protection de la plateforme ;
- modifier, ameliorer ou retirer une fonctionnalite pour des raisons de securite, conformite, performance ou strategie commerciale.

5. Responsabilites du client proprietaire

Le client proprietaire est responsable de la configuration de ses routeurs, de la qualite de sa connexion Internet, de la disponibilite de son energie electrique, de la couverture de son reseau Wi-Fi et de l'utilisation conforme des tickets qu'il vend.

Il lui appartient de verifier que ses forfaits, prix, stocks de tickets, informations commerciales et liens de vente sont corrects avant toute commercialisation.

SkyConnect ne peut pas etre tenue responsable des interruptions ou dysfonctionnements lies :

- au routeur du client ;
- au fournisseur Internet du client ;
- a une coupure d'electricite ;
- a une mauvaise configuration MikroTik ou reseau ;
- a une erreur d'importation de tickets ;
- a une mauvaise utilisation du tableau de bord par le client ;
- a un incident externe hors du controle raisonnable de SkyConnect.

6. Utilisation interdite

Il est interdit d'utiliser SkyConnect pour :

- vendre des acces Internet sans autorisation lorsque la loi l'exige ;
- commettre une fraude, une usurpation d'identite ou une tromperie commerciale ;
- contourner les systemes de securite de la plateforme ;
- acceder au compte ou aux donnees d'un tiers sans autorisation ;
- diffuser du spam, des virus, contenus illicites ou activites de piratage ;
- perturber le fonctionnement de SkyConnect, de ses serveurs, de ses paiements ou de ses clients ;
- copier, revendre, reproduire ou exploiter la plateforme sans autorisation ecrite.

Tout abus peut entrainer la suspension immediate du compte, sans prejudice des recours techniques, commerciaux ou judiciaires possibles.

7. Paiements, tickets et remboursements

Les paiements effectues via SkyConnect permettent l'achat ou la gestion de tickets Wi-Fi selon les forfaits disponibles.

Un ticket est considere comme livre lorsque le code, lien, identifiant ou information d'acces est affiche, envoye ou rendu disponible a l'utilisateur selon le processus prevu.

Les remboursements ne sont pas automatiques. Ils peuvent etre analyses au cas par cas, notamment en cas de double paiement, ticket non livre, erreur technique prouvee ou incident imputable a la plateforme.

SkyConnect peut refuser un remboursement si le ticket a deja ete utilise, si l'erreur provient du client proprietaire, du routeur, du fournisseur Internet, d'une mauvaise configuration ou d'une utilisation abusive.

8. Donnees personnelles et confidentialite

SkyConnect collecte uniquement les donnees necessaires au fonctionnement, a la securite, au support et a la tracabilite de la plateforme.

Ces donnees peuvent inclure :

- nom et prenom ;
- email ;
- telephone ;
- nom du commerce ou de l'entreprise ;
- ville et pays ;
- historique des commandes ;
- paiements et references de transaction ;
- tickets livres ;
- adresses IP ;
- logs de connexion ;
- actions realisees dans le back-office ;
- messages de support.

Ces informations servent a :

- creer et securiser les comptes ;
- traiter les commandes et paiements ;
- livrer les tickets ;
- assurer le support client ;
- prevenir la fraude ;
- tracer les operations sensibles ;
- proteger SkyConnect, ses clients et les utilisateurs finaux.

SkyConnect s'engage a ne pas vendre les donnees personnelles a des tiers. Certaines donnees peuvent etre partagees avec des prestataires techniques ou de paiement lorsque cela est necessaire au fonctionnement du service.

9. Tracabilite et audit

Pour proteger la plateforme, les clients et les operations commerciales, SkyConnect peut enregistrer certaines actions sensibles, notamment :

- connexions et deconnexions ;
- creation ou modification d'utilisateur ;
- importation de tickets ;
- modification de forfaits ;
- vente de tickets ;
- paiement confirme ou echoue ;
- remboursement ;
- modification de routeur ;
- webhook ou notification de paiement ;
- changement de parametres globaux.

Ces journaux permettent de savoir qui a fait quoi, quand et depuis quel contexte technique. Ils sont utilises pour la securite, le support, la verification interne et la protection des interets de l'entreprise.

10. Securite des comptes

Chaque utilisateur est responsable de la confidentialite de ses identifiants. Il doit choisir un mot de passe suffisamment fort et ne pas partager son compte avec une personne non autorisee.

SkyConnect peut bloquer temporairement ou definitivement un compte en cas de suspicion de compromission, d'acces non autorise ou d'activite inhabituelle.

11. Propriete intellectuelle

La marque SkyConnect, le logo, les interfaces, textes, designs, codes, composants, tableaux de bord, documents, modeles et elements graphiques appartiennent a SkyConnect ou a ses ayants droit.

Aucune reproduction, modification, distribution, revente, extraction, copie ou exploitation commerciale n'est autorisee sans accord ecrit prealable.

12. Disponibilite et evolution du service

SkyConnect met en oeuvre des efforts raisonnables pour offrir un service fiable et performant. Toutefois, la plateforme peut etre temporairement indisponible pour maintenance, mise a jour, securite, incident technique ou cause externe.

SkyConnect peut faire evoluer ses fonctionnalites, tarifs, modules, interfaces et conditions afin d'ameliorer le service, renforcer la securite ou adapter son modele economique.

13. Limitation de responsabilite

Dans la limite permise par la loi, SkyConnect ne pourra etre tenue responsable des pertes indirectes, pertes de revenus, pertes de clients, pertes de donnees, interruptions d'activite, mauvaise configuration reseau, indisponibilite Internet, usage frauduleux d'un compte ou dommage resultant d'un evenement hors de son controle raisonnable.

La responsabilite de SkyConnect, si elle devait etre engagee, sera limitee aux montants effectivement payes a SkyConnect pour le service concerne pendant la periode directement liee a l'incident.

14. Suspension et resiliation

SkyConnect peut suspendre ou resilier l'acces d'un utilisateur en cas de violation des presentes conditions, risque de securite, impaye, fraude, abus, comportement prejudiciable ou demande d'une autorite competente.

Le client peut cesser d'utiliser la plateforme a tout moment, sous reserve de respecter ses obligations de paiement, de support envers ses propres utilisateurs et de conservation des informations necessaires a ses operations.

15. Droit applicable

Les presentes conditions sont interpretees selon le droit applicable au pays d'exploitation principal de SkyConnect, sauf disposition legale contraire. En cas de litige, les parties rechercheront d'abord une solution amiable avant toute procedure.

16. Contact

Pour toute question concernant ces conditions, la confidentialite, un paiement, un compte ou une demande de support, l'utilisateur peut contacter l'equipe SkyConnect via les canaux officiels indiques sur la plateforme.
TEXT;
    }

    public static function defaultPrivacy()
    {
        return <<<'TEXT'
Politique de confidentialite SkyConnect

Date de derniere mise a jour : 06 juin 2026

SkyConnect respecte la confidentialite des utilisateurs et collecte uniquement les donnees necessaires au fonctionnement de la plateforme.

1. Donnees collectees

SkyConnect peut collecter le nom, l'email, le telephone, le nom du commerce, la ville, le pays, les commandes, les paiements, les tickets livres, les logs techniques, les adresses IP et les messages de support.

2. Utilisation des donnees

Ces donnees sont utilisees pour creer les comptes, securiser les acces, traiter les paiements, livrer les tickets, fournir le support, prevenir la fraude, auditer les operations sensibles et proteger les interets de SkyConnect.

3. Partage des donnees

SkyConnect ne vend pas les donnees personnelles. Certaines informations peuvent etre transmises a des prestataires techniques, hebergeurs, services email, fournisseurs de paiement ou autorites competentes lorsque cela est necessaire ou legalement requis.

4. Securite

SkyConnect met en place des mesures raisonnables pour proteger les donnees contre l'acces non autorise, la perte, l'alteration ou l'utilisation abusive.

5. Conservation

Les donnees sont conservees pendant la duree necessaire au service, a la securite, a la comptabilite, au support, a l'audit et au respect des obligations legales.

6. Droits des utilisateurs

Selon la loi applicable, l'utilisateur peut demander l'acces, la correction ou la suppression de certaines donnees personnelles, sous reserve des obligations legales, de securite, de paiement ou de tracabilite de SkyConnect.

7. Contact

Toute demande relative aux donnees personnelles peut etre adressee a l'equipe SkyConnect via les canaux officiels de support.
TEXT;
    }

    private static function encodeValue($value, $secret)
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $secret ? Crypt::encryptString((string) $value) : (string) $value;
    }

    private static function decodeValue($value, $secret)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! $secret) {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (Throwable $exception) {
            return null;
        }
    }

    private static function groupFor($key)
    {
        foreach (self::definitions() as $group => $definitionGroup) {
            if (array_key_exists($key, $definitionGroup['settings'])) {
                return $group;
            }
        }

        return 'general';
    }

    private static function tableReady()
    {
        try {
            return Schema::hasTable('settings');
        } catch (Throwable $exception) {
            return false;
        }
    }
}
