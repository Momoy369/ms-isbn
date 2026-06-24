<h4>Audit ISBN</h4>

<table class="table">

    <tr>
        <th>Rule</th>
        <th>Status</th>
        <th>Keterangan</th>
    </tr>

    @foreach ($book->audits as $audit)
        <tr>

            <td>
                {{ $audit->rule }}
            </td>

            <td>

                @if ($audit->passed)
                    <span class="badge badge-success">

                        READY FOR ISBN

                    </span>
                @else
                    <span class="badge badge-danger">

                        PERLU REVISI

                    </span>
                @endif

            </td>

            <td>
                {{ $audit->message }}
            </td>

        </tr>
    @endforeach

</table>
