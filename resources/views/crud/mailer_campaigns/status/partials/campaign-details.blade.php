<div class="columns is-multiline">
    <div class="table-container column is-12">
        @if($item->stats)
            <table class="table is-fullwidth">
                <thead>
                    <tr>
                        @foreach($item->stats->getSupportedProperties() as $property)
                            <th>{{ $property['name'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @foreach($item->stats->getSupportedProperties() as $key => $property)
                            <td>
                                {{ $item->stats->getStringValueFor($key) }}
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>

            <div class="columns">
                <div class="column is-6">
                    <h2 class="subtitle">Clicks Breakdown</h2>
                    <table class="table is-fullwidth">
                        <thead>
                            <tr>
                            <th>Url</th>
                            <th style="width:10%">Total Clicks</th>
                            <th style="width:10%">Unique Users</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(\Hellomayaagency\Enso\Mailer\CampaignLinkBreakdown::get($item)->sortByDesc(function ($link_stats) {
                                return $link_stats->total;
                            }) as $link => $link_stats)
                                <tr>
                                    <td><a href="{{ $link }}" target="_blank" rel="noopener noreferrer">{{ $link }}</a></td>
                                    <td>{{ $link_stats->total }}</td>
                                    <td>{{ $link_stats->unique }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <p>No stats have been generated yet for this campaign</p>
        @endif
    </div>
</div>
