# # language: fr
# Fonctionnalité: Logout
#   Deconnexion
#
#   Contexte:
#     Soit un utilisateur admin avec l'e-mail "joe@joe.com" et le mot de passe "password"
#
#   Scénario: Déconnexion
#     Etant donné que je suis connecté avec "joe@joe.com"
#     Quand je me rends sur la route "logout"
#     Alors je dois etre redirigé sur la route "login"
#     Et je ne dois pas voir "joe@joe.com"
#     Et je dois voir "Connectez-vous" dans la barre de haut
