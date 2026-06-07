# Roles back-office SkyConnect

## Super admin

Acces total a toute la plateforme.

Permissions :
- dashboard global ;
- gestion des admins et utilisateurs ;
- gestion routeurs, forfaits, tickets ;
- paiements, exports, rapports ;
- parametres sensibles ;
- logs et audit.

## Admin

Role operationnel principal de l'equipe SkyConnect.

Permissions :
- gerer les clients ;
- gerer les routeurs ;
- gerer les forfaits ;
- gerer les tickets ;
- gerer les paiements ;
- traiter le support.

## Agent support

Role de consultation et assistance.

Permissions :
- consulter les clients ;
- consulter les ventes ;
- consulter les tickets ;
- consulter les paiements ;
- consulter les demandes support.

Restrictions :
- ne modifie pas les parametres sensibles ;
- ne supprime pas de donnees ;
- ne gere pas les admins.

## Comptable

Role financier.

Permissions :
- consulter les paiements ;
- exporter les paiements ;
- consulter les rapports ;
- exporter les rapports fiscaux.

Restrictions :
- pas de gestion routeurs ;
- pas de gestion tickets ;
- pas de parametres techniques.

## Technicien

Role technique reseau.

Permissions :
- gerer les routeurs ;
- gerer les tickets ;
- consulter les diagnostics ;
- consulter les stocks.

Restrictions :
- pas de gestion admins ;
- pas de rapports financiers sensibles.

## Client proprietaire

Ce role n'est pas un role back-office global.

Il accede a `/dashboard` et voit uniquement :
- ses routeurs ;
- ses forfaits ;
- ses tickets ;
- ses ventes ;
- ses paiements ;
- ses parametres.
