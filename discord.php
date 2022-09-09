<?php

include __DIR__.'/vendor/autoload.php';

use Discord\Discord;
use Discord\Parts\Channel\Message;
use Psr\Http\Message\ResponseInterface;
use React\Http\Browser;
use Discord\Parts\Embed\Author;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Embed\Footer;
use function Discord\getColor;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Command\Command;
use Discord\Parts\Interactions\Command\Option;
use Discord\DiscordCommandClient;

$discord = new DiscordCommandClient([
    'token' => '',
    'prefix' => '!'
]);

$url_base = 'http://localhost/api/v1/discord/';

$discord->registerCommand('player', function (Message $message, $params) use ($discord) {
    if (!$params) {
        return 'Utilize o comando da seguinte forma: !player "name"';
    }

    global $url_base;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url_base . 'searchcharacter');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, ['name' => $params[0]]);
    $output = curl_exec($curl);
    curl_close($curl);
    $output_decode = json_decode($output);

    if (empty($output_decode)) {
        return 'Nenhum player encontrado.';
    }
    if (isset($output_decode->error)) {
        return 'Nenhum player encontrado.';
    }
    if ($output_decode->status == true) {
        $player_status = ':green_circle: Online';
    } else {
        $player_status = ':red_circle: Offline';
    }
    if ($output_decode->vocation == 'Druid') {
        $player_vocation = ':magic_wand: Druid';
    }
    if ($output_decode->vocation == 'Sorcerer') {
        $player_vocation = ':magic_wand: Sorcerer';
    }
    if ($output_decode->vocation == 'Knight') {
        $player_vocation = ':crossed_swords: Knight';
    }
    if ($output_decode->vocation == 'Paladin') {
        $player_vocation = ':bow_and_arrow: Paladin';
    }

    $embed = new Embed($discord);
    $embed
        ->setAuthor('', '', '')
        ->setColor('#32a854')
        ->setTitle($output_decode->name)
        ->setUrl('')
        ->addFieldValues('Level:', $output_decode->level, true)
        ->addFieldValues('Vocation:', $player_vocation, true)
        ->addFieldValues('Status:', "$player_status", true)
        ->setImage('')
        ->setFooter('', '')
        ->setTimestamp(strtotime(date('m/d/Y h:i:s')))
        ->setThumbnail($output_decode->outfit->image_url);
    $builder = MessageBuilder::new()->addEmbed($embed);
    $message->reply($builder);
});

$discord->registerCommand('boosted', function (Message $message, $params) use ($discord) {
    global $url_base;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url_base . 'boosted');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($curl);
    curl_close($curl);
    $output_decode = json_decode($output);

    if (empty($output_decode)) {
        return 'Algo deu errado.';
    }
    if (isset($output_decode->error)) {
        return 'Algo deu errado.';
    }

    $embed = new Embed($discord);
    $embed
        ->setAuthor('', '', '')
        ->setColor('#32a854')
        ->setTitle('Boosted Today')
        ->setUrl('')
        ->addFieldValues('Creature', $output_decode->boostedcreature, true)
        ->addFieldValues('Boss', $output_decode->boostedboss, true)
        ->setImage('')
        ->setFooter('', '')
        ->setTimestamp(strtotime(date('m/d/Y h:i:s')))
        ->setThumbnail('');
    $builder = MessageBuilder::new()->addEmbed($embed);
    $message->reply($builder);
});

$discord->run();