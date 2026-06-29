<div>
    {{-- Tombol Chat Floating --}}
    <button wire:click="openChat"
        class="position-fixed d-flex align-items-center justify-content-center"
        style="bottom:90px;right:calc(50% - 220px);width:52px;height:52px;border-radius:50%;
               background:#dc3545;color:#fff;border:none;box-shadow:0 2px 8px rgba(0,0,0,0.2);z-index:90;">
            <i class="fas fa-comment-dots" style="font-size:22px; vertical-align: middle;"></i>
        @if($this->unreadCount > 0)
            <span class="position-absolute badge bg-warning text-dark rounded-pill"
                style="top:-4px;right:-4px;font-size:10px;">
                {{ $this->unreadCount }}
            </span>
        @endif
    </button>

    {{-- Modal Chat --}}
    @if($showChat)
        <div class="position-fixed top-0 start-0 w-100 h-100 d-flex flex-column"
            style="background:rgba(0,0,0,0.5);z-index:999;">
            <div class="bg-white d-flex flex-column"
                style="max-width:480px;width:100%;margin:0 auto;height:75vh;margin-top:auto;border-radius:20px 20px 0 0;">

                {{-- Header --}}
                <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
                    <div>
                        <div class="fw-semibold small">
                            {{ $this->myType === 'driver' ? 'Chat dengan Customer' : 'Chat dengan Driver' }}
                        </div>
                        <div class="text-muted" style="font-size:11px;">{{ $order->order_number }}</div>
                    </div>
                    <button wire:click="closeChat" class="btn btn-sm btn-light rounded-circle"
                        style="width:32px;height:32px;">✕</button>
                </div>

                {{-- Body Messages --}}
                <div class="flex-grow-1 overflow-auto p-3" id="chat-messages" style="background:#f8f9fa;">
                    @forelse($this->messages as $msg)
                        <div class="d-flex mb-2 {{ $msg->sender_type === $this->myType ? 'justify-content-end' : 'justify-content-start' }}">
                            <div class="{{ $msg->sender_type === $this->myType ? 'bg-danger text-white' : 'bg-white' }} rounded-3 p-2"
                                style="max-width:75%;box-shadow:0 1px 2px rgba(0,0,0,0.08);">
                                @if($msg->photo)
                                    <img src="{{ \Storage::url($msg->photo) }}"
                                        class="rounded-2 mb-1 w-100" style="max-height:160px;object-fit:cover;">
                                @endif
                                @if($msg->message)
                                    <div class="small">{{ $msg->message }}</div>
                                @endif
                                <div class="{{ $msg->sender_type === $this->myType ? 'text-white-50' : 'text-muted' }}"
                                    style="font-size:10px;">
                                    {{ $msg->created_at->format('H:i') }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted small py-4">
                            Belum ada percakapan. Mulai chat untuk menanyakan sesuatu.
                        </div>
                    @endforelse
                </div>

                {{-- Input --}}
                <div class="p-3 border-top">
                    @if(!$this->canChat)
                        <div class="text-muted small text-center py-2">
                            Chat tidak tersedia untuk status order ini.
                        </div>
                    @else
                        @if($error)
                            <div class="alert alert-danger py-1 small rounded-3 mb-2">{{ $error }}</div>
                        @endif

                        @if($newPhoto)
                            <div class="mb-2 position-relative d-inline-block">
                                <img src="{{ $newPhoto->temporaryUrl() }}" class="rounded-3" style="height:60px;">
                                <button wire:click="$set('newPhoto', null)"
                                    class="btn btn-danger btn-sm position-absolute top-0 end-0 rounded-circle p-0 d-flex align-items-center justify-content-center"
                                    style="width:20px;height:20px;font-size:10px;transform:translate(50%,-50%);">✕</button>
                            </div>
                        @endif

                        <div class="d-flex gap-2 align-items-end">
                            <label class="btn btn-light rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                style="width:38px;height:38px;cursor:pointer;">
                                <i class="fas fa-camera" style="font-size:22px; vertical-align: middle;"></i>
                                <input wire:model="newPhoto" type="file" accept="image/*" class="d-none">
                            </label>

                            <textarea wire:model="newMessage" rows="1"
                                placeholder="Tulis pesan..."
                                class="form-control rounded-3"
                                style="resize:none;"
                                wire:keydown.enter.prevent="sendMessage"></textarea>

                            <button wire:click="sendMessage" wire:loading.attr="disabled"
                                class="btn btn-danger rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                style="width:38px;height:38px;">
                                ➤
                            </button>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    @endif
</div>

@script
<script>
    $wire.on('refresh-chat', () => {
        setTimeout(() => {
            const el = document.getElementById('chat-messages');
            if (el) el.scrollTop = el.scrollHeight;
        }, 100);
    });

    function initChatEcho() {
        if (typeof window.Echo === 'undefined') {
            setTimeout(initChatEcho, 300);
            return;
        }

        window.Echo.private('order.{{ $order->id }}.chat')
            .listen('.message.sent', (data) => {
                $wire.dispatch('refresh-chat');
                $wire.$refresh();
            });
    }

    initChatEcho();
</script>
@endscript