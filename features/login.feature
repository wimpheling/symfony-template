# language: fr
Fonctionnalité: Login
  Contexte:
    Etant donné que the database is empty
    #Soit the fixtures file "clients.yml" is loaded
    Soit un utilisateur avec l'e-mail "joe@joe.com" et le mot de passe "password"

  Scénario: Email Non existant
    Quand je vais sur "/login"
    Et que je remplis "form[email]" avec "michael@michael.com"
    Et que je remplis "form[motDePasse]" avec "test"
    Et que je presse "Me connecter"
    Alors je devrais être sur "/login"
    Et la réponse devrait contenir "Utilisateur non trouvé ou mot de passe incorrect"

  Scénario: Mot de passe invalide
    Quand je vais sur "/login"
    Et que je remplis "form[email]" avec "joe@joe.com"
    Et que je remplis "form[motDePasse]" avec "mauvaismotdepasse"
    Et que je presse "Me connecter"
    Alors je devrais être sur "/login"
    Et la réponse devrait contenir "Utilisateur non trouvé ou mot de passe incorrect"

  Scénario: Login correct
    Quand je vais sur "/login"
    Alors le code de status de la réponse devrait être 200
    Lorsque je remplis "form[email]" avec "joe@joe.com"
    Et que je remplis "form[motDePasse]" avec "password"
    Et que je presse "Me connecter"
    Alors je devrais être sur "/"
    Et le code de status de la réponse devrait être 200