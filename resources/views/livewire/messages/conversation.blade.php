<div class="direct-message-page">
    <a class="member-profile-back" href="{{ route('messages.index') }}">&larr; Back to Messages</a>
    <section class="direct-message-card">
        <header class="direct-message-header">
            <a href="{{ route('members.show', $member) }}" class="message-thread-avatar">
                @if ($member->profile_photo_path)
                    <img src="{{ asset('storage/'.$member->profile_photo_path) }}" alt="">
                @else
                    {{ strtoupper(substr($member->name, 0, 1)) }}
                @endif
            </a>
            <div><b>{{ $member->name }}</b><small>Live conversation</small></div>
            <a href="{{ route('members.show', $member) }}">View profile</a>
        </header>

        <div class="direct-message-list" aria-live="polite" aria-relevant="additions text">
            @forelse ($messages as $message)
                <article wire:key="message-{{ $message['id'] }}" id="{{ $loop->last ? 'latest-message' : 'message-'.$message['id'] }}" class="direct-message {{ (int) $message['sender_id'] === (int) auth()->id() ? 'is-mine' : '' }}">
                    <p>{{ $message['body'] }}</p>
                    <small class="direct-message-meta">{{ $message['sent_at_label'] }}</small>
                </article>
            @empty
                <div class="direct-message-empty"><b>Say hello to {{ $member->name }}.</b><span>Messages are live only and disappear when you leave this conversation.</span></div>
            @endforelse
        </div>

        <form class="direct-message-compose" wire:submit="send">
            <label class="sr-only" for="message-body">Your message</label>
            <textarea id="message-body" wire:model="body" rows="2" maxlength="2000" required placeholder="Write a message&hellip;"></textarea>
            <div class="direct-message-compose__actions">
                <button type="submit" wire:loading.attr="disabled" wire:target="send">
                    <span wire:loading.remove wire:target="send">Send <b aria-hidden="true">&rarr;</b></span>
                    <span wire:loading wire:target="send">Sending&hellip;</span>
                </button>
            </div>
            <x-input-error class="direct-message-error" :messages="$errors->get('body')" />
        </form>
    </section>

    @script
        <script>
            let shouldStickToBottom = true;
            const getMessageList = () => $wire.$el.querySelector('.direct-message-list');
            const updateScrollIntent = () => {
                const list = getMessageList();
                if (list) shouldStickToBottom = list.scrollHeight - list.clientHeight - list.scrollTop < 48;
            };
            const scrollToLatestMessage = (force = false) => requestAnimationFrame(() => {
                const list = getMessageList();
                if (list && (force || shouldStickToBottom)) {
                    list.scrollTop = list.scrollHeight;
                    shouldStickToBottom = true;
                }
            });

            getMessageList()?.addEventListener('scroll', updateScrollIntent);
            scrollToLatestMessage(true);
            $wire.on('message-updated', () => scrollToLatestMessage());
        </script>
    @endscript
</div>
