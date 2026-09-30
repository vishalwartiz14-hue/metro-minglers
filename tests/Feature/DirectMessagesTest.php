<?php

use App\Events\DirectMessageRead;
use App\Events\DirectMessageSent;
use App\Events\DirectMessageUpdated;
use App\Livewire\Messages\Conversation;
use App\Livewire\Messages\Inbox;
use App\Models\DirectMessage;
use App\Models\User;
use App\Models\UserConnection;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

test('direct messages persist through send, inbox, read, and edit flows', function () {
    $sender = User::factory()->create();
    $recipient = User::factory()->create();

    UserConnection::query()->create([
        'sender_id' => $sender->id,
        'recipient_id' => $recipient->id,
        'status' => UserConnection::ACCEPTED,
    ]);

    Event::fake([
        DirectMessageRead::class,
        DirectMessageSent::class,
        DirectMessageUpdated::class,
    ]);

    $this->actingAs($sender);

    Livewire::test(Conversation::class, ['member' => $recipient])
        ->set('body', 'We should go to the next Mingle!')
        ->call('send')
        ->assertHasNoErrors()
        ->assertSet('body', '');

    $message = DirectMessage::query()->sole();

    $this->assertDatabaseHas('direct_messages', [
        'id' => $message->id,
        'sender_id' => $sender->id,
        'recipient_id' => $recipient->id,
        'body' => 'We should go to the next Mingle!',
        'read_at' => null,
    ]);
    Event::assertDispatched(DirectMessageSent::class, fn (DirectMessageSent $event) => $event->message->is($message));

    $this->actingAs($recipient);

    Livewire::test(Inbox::class)
        ->assertSee($sender->name)
        ->assertSee('We should go to the next Mingle!')
        ->assertSee('1 unread');

    Livewire::test(Conversation::class, ['member' => $sender])
        ->assertSee('We should go to the next Mingle!');

    $this->assertNotNull($message->fresh()->read_at);
    Event::assertDispatched(DirectMessageRead::class, fn (DirectMessageRead $event) =>
        $event->readerId === $recipient->id
        && $event->senderId === $sender->id
        && in_array($message->id, $event->messageIds, true)
    );

    $this->actingAs($sender);

    Livewire::test(Conversation::class, ['member' => $recipient])
        ->call('editMessage', $message->id)
        ->assertSet('body', 'We should go to the next Mingle!')
        ->set('body', 'See you at the Mingle!')
        ->call('updateMessage')
        ->assertHasNoErrors();

    $message->refresh();

    $this->assertSame('See you at the Mingle!', $message->body);
    $this->assertNotNull($message->edited_at);
    $this->assertNotNull($message->read_at);
    Event::assertDispatched(DirectMessageUpdated::class, fn (DirectMessageUpdated $event) => $event->message->is($message));
});
