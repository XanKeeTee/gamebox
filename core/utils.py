import os
import requests
import re
import datetime
from django.core.cache import cache
from dotenv import load_dotenv
from howlongtobeatpy import HowLongToBeat

load_dotenv()

TWITCH_CLIENT_ID = os.getenv('TWITCH_CLIENT_ID')
TWITCH_CLIENT_SECRET = os.getenv('TWITCH_CLIENT_SECRET')

def get_igdb_token():
    token = cache.get('igdb_token')
    if token:
        return token

    url = f"https://id.twitch.tv/oauth2/token?client_id={TWITCH_CLIENT_ID}&client_secret={TWITCH_CLIENT_SECRET}&grant_type=client_credentials"
    
    try:
        response = requests.post(url, timeout=10)
        if response.status_code == 200:
            data = response.json()
            token = data.get('access_token')
            expires_in = data.get('expires_in', 3600)
            if token:
                cache.set('igdb_token', token, timeout=expires_in - 300)
                return token
    except:
        pass
    return None

def get_trending_games():
    trending = cache.get('trending_games_global')
    if trending:
        return trending

    token = get_igdb_token()
    if not token:
        return []

    url = "https://api.igdb.com/v4/games"
    headers = {
        'Client-ID': TWITCH_CLIENT_ID,
        'Authorization': f'Bearer {token}',
    }
    query = "fields name, cover.url, total_rating; where total_rating > 85 & cover != null; sort follows desc; limit 5;"
    
    try:
        response = requests.post(url, headers=headers, data=query, timeout=10)
        if response.status_code == 200:
            games = response.json()
            for game in games:
                cover = game.get('cover')
                if isinstance(cover, dict) and cover.get('url'):
                    game['cover']['url'] = str(cover['url']).replace('t_thumb', 't_cover_big')
            cache.set('trending_games_global', games, timeout=3600)
            return games
    except:
        pass
    return []

def search_igdb_games(search_query, sort_by='popular'):
    cache_key = f"search_{search_query}_{sort_by}"
    cached_search = cache.get(cache_key)
    if cached_search:
        return cached_search

    token = get_igdb_token()
    if not token:
        return []

    url = "https://api.igdb.com/v4/games"
    headers = {'Client-ID': TWITCH_CLIENT_ID, 'Authorization': f'Bearer {token}'}
    
    query = f'search "{search_query}"; fields name, cover.url, first_release_date, total_rating, follows; where cover != null; limit 50;'
    
    try:
        response = requests.post(url, headers=headers, data=query, timeout=10)
        if response.status_code == 200:
            games = response.json()
            for game in games:
                cover = game.get('cover')
                if isinstance(cover, dict) and cover.get('url'):
                    game['cover']['url'] = str(cover['url']).replace('t_thumb', 't_cover_big')
                
                first_release_date = game.get('first_release_date')
                if first_release_date and isinstance(first_release_date, (int, float)):
                    game['year'] = datetime.datetime.fromtimestamp(first_release_date).year
            
            if sort_by == 'top_rated':
                games = sorted(games, key=lambda k: k.get('total_rating') or 0, reverse=True)
            else:
                games = sorted(games, key=lambda k: k.get('follows') or 0, reverse=True)
                
            final_results = games[:24]
            cache.set(cache_key, final_results, timeout=3600)
            return final_results
    except:
        pass
    return []

def get_igdb_game_details(game_id):
    cache_key = f'full_detail_v100_{game_id}'
    cached_game = cache.get(cache_key)
    if cached_game:
        return cached_game

    token = get_igdb_token()
    if not token:
        return None

    headers = {'Client-ID': TWITCH_CLIENT_ID, 'Authorization': f'Bearer {token}'}
    query = f"fields name, cover.url, summary, first_release_date, total_rating, genres.name, platforms.name, screenshots.url, involved_companies.company.name, involved_companies.developer, videos.video_id, videos.name, websites.category, websites.url; where id = {game_id};"
    
    try:
        response = requests.post("https://api.igdb.com/v4/games", headers=headers, data=query, timeout=10)
        if response.status_code != 200:
            return None
            
        data = response.json()
        if not data or not isinstance(data, list):
            return None
            
        game = data[0]
        if not isinstance(game, dict):
            return None

        game['achievements'] = []
        STEAM_KEY = os.getenv('STEAM_API_KEY')
        steam_id = None
        
        websites = game.get('websites', [])
        if isinstance(websites, list):
            for site in websites:
                if isinstance(site, dict) and site.get('category') == 13 and site.get('url'):
                    match = re.search(r'/app/(\d+)', str(site['url']))
                    if match:
                        steam_id = match.group(1)
                        break

        if steam_id and STEAM_KEY:
            try:
                s_url = f"https://api.steampowered.com/ISteamUserStats/GetSchemaForGame/v2/?key={STEAM_KEY}&appid={steam_id}&l=spanish"
                s_res = requests.get(s_url, timeout=5)
                if s_res.status_code == 200:
                    s_data = s_res.json()
                    raw = s_data.get('game', {}).get('availableGameStats', {}).get('achievements', [])
                    if isinstance(raw, list):
                        for a in raw:
                            if isinstance(a, dict):
                                game['achievements'].append({
                                    'name': a.get('displayName', 'Logro'),
                                    'description': a.get('description', 'Logro oculto.'),
                                    'rarity': 'Bronce'
                                })
            except:
                pass

        if not game.get('achievements'):
            game['achievements'] = [
                {'name': 'Primeros Pasos', 'description': f'Inicia tu aventura en {game.get("name", "este juego")}.', 'rarity': 'Bronce'},
                {'name': 'Héroe de Leyenda', 'description': 'Completa la campaña principal.', 'rarity': 'Oro'}
            ]

        cover = game.get('cover')
        if isinstance(cover, dict) and cover.get('url'):
            game['cover']['url'] = str(cover['url']).replace('t_thumb', 't_cover_big')
        
        screenshots = game.get('screenshots', [])
        if isinstance(screenshots, list):
            for s in screenshots:
                if isinstance(s, dict) and s.get('url'):
                    s['url'] = str(s['url']).replace('t_thumb', 't_1080p')
        
        first_release_date = game.get('first_release_date')
        if first_release_date and isinstance(first_release_date, (int, float)):
            game['formatted_date'] = datetime.datetime.fromtimestamp(first_release_date).strftime('%d %b, %Y')
        else:
            game['formatted_date'] = "TBA"
        
        devs = []
        involved_companies = game.get('involved_companies', [])
        if isinstance(involved_companies, list):
            for c in involved_companies:
                if isinstance(c, dict) and c.get('developer'):
                    comp = c.get('company')
                    if isinstance(comp, dict) and comp.get('name'):
                        devs.append(str(comp['name']))
        game['developers'] = ", ".join(devs) if devs else "Desconocido"

        videos = game.get('videos', [])
        if isinstance(videos, list):
            for v in videos:
                if isinstance(v, dict) and v.get('video_id'):
                    v['youtube_url'] = f"https://www.youtube.com/watch?v={v['video_id']}"

        game['hltb'] = {'main_story': '--', 'main_extra': '--', 'completionist': '--'}
        game_name = game.get('name')
        if game_name and isinstance(game_name, str):
            try:
                results = HowLongToBeat().search(game_name)
                if results and isinstance(results, list):
                    best = max(results, key=lambda x: getattr(x, 'similarity', 0))
                    game['hltb'] = {
                        'main_story': f"{getattr(best, 'main_story', '--')}h",
                        'main_extra': f"{getattr(best, 'main_extra', '--')}h",
                        'completionist': f"{getattr(best, 'completionist', '--')}h"
                    }
            except:
                pass

        cache.set(cache_key, game, timeout=86400)
        return game
    except:
        return None