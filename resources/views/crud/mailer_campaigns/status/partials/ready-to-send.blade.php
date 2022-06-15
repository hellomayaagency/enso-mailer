<div class="columns is-multiline">
    <div class="column is-12">This Email will send to: <strong>{{ $item->getAudienceUsersCount()  }}</strong> email address{{ ($item->getAudienceUsersCount() === 1) ? '' : 'es' }}.</div>
</div>

<hr>

<div class="columns is-marginless is-multiline">
    <form
        method="POST"
        action="{{ route('admin.mailer.campaigns.status.preview-send', $item->getKey()) }}"
        accept-charset="UTF-8"
        class="column is-12 background-is-off-white"
    >
        {{ csrf_field() }}

        <div class="columns is-multiline">
            <div class="column is-10">
                <input
                class="input is-fullwidth{{ ($errors->has('preview_general') || $errors->has('preview_recipients')) ? ' is-danger' : '' }}"
                id="preview_recipients"
                name="preview_recipients"
                type="text"
                placeholder="Comma separated list of emails to send to"
                value="{{ old('preview_recipients') }}"
                >
            </div>
            <div class="column is-2"><button class="button is-success is-fullwidth" type="submit">Preview Send</button></div>
        </div>
    </form>
</div>

{{-- Scheduled sending works by queuing a job for a set time. As such, it shouldn't be available when using the
  -- 'sync' queue driver as it has no capability to do this.
  --}}
@if (Config::get('queue.default') !== 'sync')
    <hr>
    <div class="columns is-marginless is-multiline">
        <form
            method="POST"
            action="{{ route('admin.mailer.campaigns.status.schedule-send', $item->getKey()) }}"
            accept-charset="UTF-8"
            class="column is-12 background-is-off-white"
        >
            {{ csrf_field() }}

            <div class="columns is-multiline">
                <div class="column is-10">
                    <enso-field-datetime
                    :input-value="null"
                    name="send_campaign_at"
                    :hide-seconds="true"
                    :field-classes="{{ json_encode(($errors->has('send_campaign_at_date') || $errors->has('send_campaign_at_hours') || $errors->has('send_campaign_at_minutes')) ? ['is-danger'] : []) }}"
                    ></enso-field-datetime>
                </div>
                <div class="column is-2"><button class="button is-success is-fullwidth" type="submit">Schedule Send</button></div>
            </div>
        </form>
    </div>
@endif

<hr>
<div class="columns is-marginless is-multiline">
  <div class="column is-2 is-offset-10">
    <a class="button is-success is-fullwidth" href="{{ route('admin.mailer.campaigns.status.send', $item->getKey()) }}">Final Send</a>
  </div>
</div>
