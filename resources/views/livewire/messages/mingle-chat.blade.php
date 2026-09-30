<section class="mingle-chat" id="mingle-chat">
    <header>
        <p class="live-section-label">MEMBER CHAT</p>
        <h3>Keep the conversation going</h3>
        <small>Live for this visit only; messages are not saved.</small>
    </header>

    <div class="mingle-chat__messages" aria-live="polite" aria-relevant="additions text">
        @forelse ($messages as $message)
            <article wire:key="mingle-message-{{ $message['id'] }}" class="mingle-chat__message {{ (int) $message['sender_id'] === (int) auth()->id() ? 'is-mine' : '' }}">
                <a href="{{ route('members.show', $message['sender_id']) }}">{{ $message['sender_name'] }}</a>
                <p>{{ $message['body'] }}</p>
                <small>{{ $message['sent_at_label'] }}</small>
            </article>
        @empty
            <p class="mingle-chat__empty">Say hello to the group and make the first connection.</p>
        @endforelse
    </div>

    <form wire:submit="send" class="mingle-chat__compose">
        <label class="sr-only" for="mingle-chat-body">Your message</label>
        <textarea id="mingle-chat-body" wire:model="body" rows="2" maxlength="2000" required placeholder="Message this Mingle&hellip;"></textarea>
        <x-input-error :messages="$errors->get('body')" />
        <button type="submit" wire:loading.attr="disabled" wire:target="send">
            <span wire:loading.remove wire:target="send">Send message &rarr;</span>
            <span wire:loading wire:target="send">Sending&hellip;</span>
        </button>
    </form>

    @script
        <script>
            const scrollMingleChat = () => requestAnimationFrame(() => {
                const list = $wire.$el.querySelector('.mingle-chat__messages');
                if (list) list.scrollTop = list.scrollHeight;
            });
            scrollMingleChat();
            $wire.on('mingle-message-updated', scrollMingleChat);
        </script>
    @endscript
</section>
