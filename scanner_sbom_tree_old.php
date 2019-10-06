<?php
  $nav_selected = "SCANNER";
  $left_buttons = "YES";
  $left_selected = "SBOMTREE";

  include("./nav.php");
 ?>

<!--Imports-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-treetable/3.2.0/css/jquery.treetable.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-treetable/3.2.0/css/jquery.treetable.theme.default.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-treetable/3.2.0/jquery.treetable.js"></script>

 <div class="right-content">
    <div class="container" id="container">
      <h3 style = "color: #01B0F1;">Scanner --> BOM Tree</h3>

      <script type="text/javascript">
        //We only use php to pull the rows from the sbom table and store them into an array
        let sbomArray = [];

        <?php
        $sql = "SELECT * from sbom";
        $result = $db->query($sql);

        if ($result->num_rows > 0) {
          while($row = $result->fetch_assoc()) {
            echo "sbomArray.push(", json_encode($row), ");\r\n";
          }
        }else {
          echo "0 results";
        }

        $result->close();
        ?>

        //Build a very nested Map
        //I did this to simulate a tree datastructure w/o actually implementing a tree datastructure (take that, ICS-340)
        let tree = new Map();        
        sbomArray.forEach(row => {
          //If the tree doesn't have the app_name, add it
          if(!tree.has(row['app_name'])){
            tree.set(row['app_name'], new Map());
          }

          //if the tree doesn't have the app_id of an app_name, add it
          if(!tree.get(row['app_name']).has(row['app_id'])){
            tree.get(row['app_name']).set(row['app_id'], new Map());
          }

          //if the tree doesn't have the cmp_name of an app_id of an app_name, add it
          if(!tree.get(row['app_name']).get(row['app_id']).has(row['cmp_name'])){
            tree.get(row['app_name']).get(row['app_id']).set(row['cmp_name'], row);
          }
        });

        //Build a table that the jQuery treetable plugin can understand
        let container = document.getElementById('container');

        let root = document.createElement('table');
        let tbody = document.createElement('tbody');

        root.appendChild(tbody);

        //These three variables keep track of unique id's and parent:child relationships.
        let idCount = 1;
        let app_nameParentId = -1;
        let app_idParentId = -1;


        //Three nested for loops to generate the table and relationships between rows. TC is O(n^2 * log n)..... Gross.

        //Loop over app_name
        tree.forEach((value, key) => {
          let tr = document.createElement('tr');
          tr.setAttribute('data-tt-id', idCount);
          tbody.appendChild(tr);

          let data = document.createElement('td');
          data.innerHTML = key;
          tr.appendChild(data);

          app_nameParentId = idCount++;

          //loop over app_id
          value.forEach((value, key) => {
            tr = document.createElement('tr');
            tr.setAttribute('data-tt-id', idCount);
            tr.setAttribute('data-tt-parent-id', app_nameParentId);
            tbody.appendChild(tr);

            let data = document.createElement('td');
            data.innerHTML = key;
            tr.appendChild(data);

            app_idParentId = idCount++;

            //loop over cmp_name
            value.forEach((value, key) => {
              tr = document.createElement('tr');
              tr.setAttribute('data-tt-id', idCount);
              tr.setAttribute('data-tt-parent-id', app_idParentId);
              tbody.appendChild(tr);

              let data = document.createElement('td');
              //data.innerHTML = Object.entries(value);
              data.innerHTML = value['cmp_name'];
              tr.appendChild(data);
            });
          });
        });

        root.setAttribute('id', 'maintreetable');
        container.appendChild(root);

        //Params for the treetable
        let params = {
          expandable: true,
          clickableNodeNames: true
        };

        //Generate tree table
        $("#maintreetable").treetable(params);
      </script>
    </div>
</div>

<?php include("./footer.php"); ?>
