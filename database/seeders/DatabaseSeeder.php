<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\LiveStream;
use App\Models\Question;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed the admin user if not exists
        if (!User::where('email', 'admin@livezinha.com')->exists()) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@livezinha.com',
                'password' => bcrypt('admin123'),
            ]);
        }

        // 2. Clear existing lives and questions to make seeding idempotent/clean
        Question::query()->delete();
        LiveStream::query()->delete();

        // 3. Create a Finished Live
        $finishedLiveStart = Carbon::now()->subDays(2)->subHours(1);
        $finishedLive = LiveStream::create([
            'title' => 'Live #1: Introdução ao Vue 3 e Laravel',
            'streamer_name' => 'Admin',
            'live_url' => 'https://twitch.tv/adminlive',
            'scheduled_at' => Carbon::now()->subDays(2),
            'status' => 'finished',
            'started_at' => $finishedLiveStart,
        ]);
 
        // Questions for finished live (all archived/answered)
        Question::create([
            'live_stream_id' => $finishedLive->id,
            'name' => 'Renato Abreu',
            'tiktok_handle' => '@renato_codes',
            'question_text' => 'Qual é a melhor forma de organizar as rotas da API no Laravel para projetos grandes?',
            'passcode' => Question::generateUniquePasscode(),
            'status' => 'archived',
            'is_tagged' => true,
            'is_hidden' => false,
            'displayed_at' => (clone $finishedLiveStart)->addMinutes(12)->addSeconds(30),
            'removed_at' => (clone $finishedLiveStart)->addMinutes(16)->addSeconds(45),
            'duration_seconds' => 255,
        ]);
 
        Question::create([
            'live_stream_id' => $finishedLive->id,
            'name' => 'Gabriela Souza',
            'tiktok_handle' => null,
            'question_text' => 'Como funciona o ciclo de vida do Vue 3 usando Composition API com a Script Setup?',
            'passcode' => Question::generateUniquePasscode(),
            'status' => 'archived',
            'is_tagged' => false,
            'is_hidden' => false,
            'displayed_at' => (clone $finishedLiveStart)->addMinutes(35)->addSeconds(10),
            'removed_at' => (clone $finishedLiveStart)->addMinutes(39)->addSeconds(40),
            'duration_seconds' => 270,
        ]);
 
        // 4. Create an Active Live (Current Live)
        $activeLiveStart = Carbon::now()->subHours(1);
        $activeLive = LiveStream::create([
            'title' => 'Live #2: Criando Apps Modernos com Vuetify',
            'streamer_name' => 'Admin',
            'live_url' => 'https://twitch.tv/adminlive',
            'scheduled_at' => Carbon::now(),
            'status' => 'active',
            'started_at' => $activeLiveStart,
        ]);
 
        // Active question (being displayed on screen)
        Question::create([
            'live_stream_id' => $activeLive->id,
            'name' => 'Felipe Castanhari',
            'tiktok_handle' => '@felipinhocast',
            'question_text' => 'Podemos utilizar Vuetify em projetos SSR com Nuxt.js ou apenas em SPA simples?',
            'passcode' => Question::generateUniquePasscode(),
            'status' => 'active',
            'is_tagged' => false,
            'is_hidden' => false,
            'displayed_at' => Carbon::now()->subMinutes(10)->addSeconds(15),
            'removed_at' => null,
            'duration_seconds' => null,
        ]);
 
        // Approved questions
        Question::create([
            'live_stream_id' => $activeLive->id,
            'name' => 'Larissa Manoela',
            'tiktok_handle' => '@larimanoela',
            'question_text' => 'O Vuetify 3 possui suporte nativo para dark mode ou temos que configurar temas manualmente?',
            'passcode' => Question::generateUniquePasscode(),
            'status' => 'approved',
            'is_tagged' => false,
            'is_hidden' => false,
        ]);
 
        Question::create([
            'live_stream_id' => $activeLive->id,
            'name' => 'Bruno Mezenga',
            'tiktok_handle' => null,
            'question_text' => 'É possível customizar as cores principais do tema do Vuetify diretamente no Javascript?',
            'passcode' => Question::generateUniquePasscode(),
            'status' => 'approved',
            'is_tagged' => false,
            'is_hidden' => false,
        ]);
 
        // Pending questions
        Question::create([
            'live_stream_id' => $activeLive->id,
            'name' => 'Juliana Paes',
            'tiktok_handle' => '@jupaes_real',
            'question_text' => 'Como integrar o Flatpickr de forma elegante dentro de um v-text-field do Vuetify?',
            'passcode' => Question::generateUniquePasscode(),
            'status' => 'pending',
            'is_tagged' => false,
            'is_hidden' => false,
        ]);
 
        Question::create([
            'live_stream_id' => $activeLive->id,
            'name' => 'Zeca Pagodinho',
            'tiktok_handle' => null,
            'question_text' => 'Quais são as principais vantagens de usar o vite-plugin-vuetify em vez de importar tudo no main.js?',
            'passcode' => Question::generateUniquePasscode(),
            'status' => 'pending',
            'is_tagged' => false,
            'is_hidden' => false,
        ]);
 
        // Archived question (answered)
        Question::create([
            'live_stream_id' => $activeLive->id,
            'name' => 'Chorão',
            'tiktok_handle' => '@chorao_eterno',
            'question_text' => 'Qual é a diferença de performance entre Vuetify e TailwindCSS no carregamento inicial da página?',
            'passcode' => Question::generateUniquePasscode(),
            'status' => 'archived',
            'is_tagged' => true,
            'is_hidden' => false,
            'displayed_at' => (clone $activeLiveStart)->addMinutes(15)->addSeconds(0),
            'removed_at' => (clone $activeLiveStart)->addMinutes(20)->addSeconds(10),
            'duration_seconds' => 310,
        ]);

        // 5. Create a Scheduled Live (Future)
        $scheduledLive = LiveStream::create([
            'title' => 'Live #3: Deploy Avançado com Docker e Laravel Sail',
            'streamer_name' => 'Admin',
            'live_url' => 'https://youtube.com/watch?v=exemplo',
            'scheduled_at' => Carbon::now()->addDays(3)->hour(20)->minute(0)->second(0),
            'status' => 'scheduled',
        ]);

        // Pending questions for the future live
        Question::create([
            'live_stream_id' => $scheduledLive->id,
            'name' => 'Pedro Bial',
            'tiktok_handle' => '@pedrobial',
            'question_text' => 'O Laravel Sail é recomendado para ambientes de produção ou devemos usar um Dockerfile próprio customizado?',
            'passcode' => Question::generateUniquePasscode(),
            'status' => 'pending',
            'is_tagged' => false,
            'is_hidden' => false,
        ]);

        Question::create([
            'live_stream_id' => $scheduledLive->id,
            'name' => 'Fátima Bernardes',
            'tiktok_handle' => null,
            'question_text' => 'Como configurar variáveis de ambiente seguras no Docker compose sem expor senhas no repositório Git?',
            'passcode' => Question::generateUniquePasscode(),
            'status' => 'pending',
            'is_tagged' => false,
            'is_hidden' => false,
        ]);
    }
}
