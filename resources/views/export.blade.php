
<a href="{{route('customer.exportFile')}}">Export File</a>
<table class="table table-striped">
    <thead>
    <th>ID</th>
    <th>Name</th>
    <th>Last Name</th>
    </thead>
    <tbody>
    @foreach($customers as $item)
        <tr>
            <td>{{$item->id}}</td>
            <td>{{$item->first_name}}</td>
            <td>{{$item->last_name}}</td>
        </tr>
    @endforeach
    </tbody>
</table>
