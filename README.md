![telegrambot](https://raw.githubusercontent.com/tetreum/telegram-rss-downloader-bot/master/1.png)

# Telegram rss downloader bot

This Telegram bot will automatically download and send back your rss feeds for offline reading.
The bot is working, but it needs a code refactor to make it easier to maintain/more readable.

1. User sets [add|list|remove] his rss feed collection 
2. When `/givemethenews` command is sent. Bot will then proceed to parse and download the entire articles, clean them and send them back as html attachment for offline reading.

Images will be converted to base64.
Gifs are begin ignored.

# Requirements
- PHP >= 7.0
- CURL
- Composer
- SSL certificate (telegram bots can only talk to https domains)

# Setup

1. mv `conf.sample.php` to `conf.php`
2. Create your telegram bot and set its data on `conf.php`
3. run `composer install`
3. Done, you may need to create cache folders and give them write perms, those are listed on config file

# Setting manual css selectors to remove

You can edit `providers.php` to add more css selectors that you want bot to remove from each article.

# ToDo

- Improve html sanitization (remove onmouseover, onclick, etc.. attributes)
- Add a "share" topbar in `header.html` to make it easier to share the original article with contacts

