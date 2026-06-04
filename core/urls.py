from django.urls import path
from . import views

urlpatterns = [
    path('', views.feed, name='feed'),
    path('search/', views.search, name='search'),
    path('game/<int:game_id>/', views.game_detail, name='game_detail'),
    path('registro/', views.registro_view, name='registro'),
    path('login/', views.login_view, name='login'),
    path('logout/', views.logout_view, name='logout'),
    path('add-backlog/<int:game_id>/', views.add_game, name='add_game'),
    path('game/<int:game_id>/review/', views.post_review, name='post_review'),
    path('profile/<str:username>/', views.profile_view, name='profile'),
    path('delete-game/<int:game_id>/', views.delete_game, name='delete_game'),

    path('search-fav/', views.search_fav_api, name='search_fav_api'),
    path('save-fav/', views.save_favorite, name='save_favorite'),
    path('remove-fav/<int:slot>/', views.remove_favorite, name='remove_favorite'),

    path('upload-story/', views.upload_story, name='upload_story'),

    path('terms/', views.terms, name='terms'),
    path('privacy/', views.privacy, name='privacy'),
    path('toggle-follow/<str:username>/', views.toggle_follow, name='toggle_follow'),
    path('profile/<str:username>/<str:list_type>/', views.follow_list, name='follow_list'),
    path('delete-review/<int:post_id>/', views.delete_review, name='delete_review'),

    path('collection/', views.collection_view, name='collection'),
]