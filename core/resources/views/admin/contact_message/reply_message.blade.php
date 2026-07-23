{{-- reply modal --}}
<div class="modal fade" id="replyContactMessageModal{{ $contactMessage->id }}" tabindex="-1" role="dialog" aria-labelledby="replyContactMessageModalLabel{{ $contactMessage->id }}" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form action="{{ route('admin.contact_message.reply') }}" method="post">
        @csrf
        <input type="hidden" name="contact_message_id" value="{{ $contactMessage->id }}">

        <div class="modal-header">
          <h5 class="modal-title" id="replyContactMessageModalLabel{{ $contactMessage->id }}">Reply to {{ $contactMessage->name }}</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          @if ($contactMessage->isReplied())
            <div class="alert alert-info">
              <strong>Previously replied</strong> by {{ $contactMessage->replied_by }} on {{ $contactMessage->replied_at->format('d M Y, h:i A') }}:
              <p class="mb-0 mt-2">{!! nl2br(e($contactMessage->reply_message)) !!}</p>
            </div>
          @endif

          <div class="form-group">
            <label>Original Message</label>
            <p class="text-muted">{!! nl2br(e($contactMessage->message)) !!}</p>
          </div>

          <div class="form-group">
            <label>Subject</label>
            <input type="text" class="form-control" name="reply_subject" value="Re: {{ $contactMessage->subject }}" required>
          </div>

          <div class="form-group">
            <label>Message</label>
            <textarea class="form-control" name="reply_message" rows="6" required></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-paper-plane"></i> Send Reply
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
