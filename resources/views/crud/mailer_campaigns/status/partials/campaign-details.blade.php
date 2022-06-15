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
    @else
      <p>No stats have been generated yet for this campaign</p>
    @endif
  </div>
</div>