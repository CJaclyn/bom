<?php
  $nav_selected = "SCANNER";
  $left_buttons = "YES";
  $left_selected = "SBOMTREE";

  include("./nav.php");
 ?>

<!--Imports-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-treetable/3.2.0/css/jquery.treetable.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-treetable/3.2.0/css/jquery.treetable.theme.default.css" />

<div class="right-content">
    <div class="container" id="container">
      <h3 style = "color: #01B0F1;">Scanner --> BOM Tree</h3>
<div>
  <table id = "bom_treetable" class = "treetable">
      <caption>
        <a href="#" onclick="$('#bom_treetable').treetable('expandAll'); return false;">Expand all</a>
        <a href="#" onclick="$('#bom_treetable').treetable('collapseAll'); return false;">Collapse all</a>
      </caption>
      <thead >
        <th>Name</th>
        <th>ID</th>
        <th>Version</th>
        <th>Status</th>
        <th>Notes</th>
      </thead>
      <tbody>
      <?php
      //finds parent data
      $sql_parent = "SELECT DISTINCT app_name from sbom order by app_name;";
      $result_parent = $db->query($sql_parent);
      $p=1;
      if ($result_parent->num_rows > 0) {
        while($row_parent = $result_parent->fetch_assoc()) {
          $app_name = $row_parent["app_name"];
          $p_id = $p;
          echo "<tr data-tt-id = '".$p_id."'>";
          echo "<td>".$app_name."</td><td></td><td></td><td></td><td></td>";
          echo "</tr>";
          $p++;
          //Finds child data
            $sql_child = "SELECT DISTINCT app_name, app_id, app_version, app_status from sbom where app_name = '".$app_name."' order by app_name;";
            $result_child = $db->query($sql_child);
            $c=1;
            if ($result_child->num_rows > 0) {
              while($row_child = $result_child->fetch_assoc()) {
                $app_name = $row_child["app_name"];
                $app_id = $row_child["app_id"];
                $app_version = $row_child["app_version"];
                $app_status = $row_child["app_status"];
                $c_id = $p_id."-".$c;
                echo "<tr data-tt-id = '".$c_id."' data-tt-parent-id='".$p_id."'>";
                echo "<td>".$app_name."</td><td>".$app_id."</td><td>".$app_version."</td><td>".$app_status."</td><td></td>";
                echo "</tr>";
                $c++;
                // output data of grandchild
                $sql_gchild = "SELECT distinct cmp_type from sbom 
                        where app_name = '".$app_name."' 
                        and app_id = '".$app_id."' 
                        and app_version = '".$app_version."'
                        and app_status = '".$app_status."'
                        order by cmp_type;";
                        $result_gchild = $db->query($sql_gchild);
                        $gc = 1;
                        if ($result_gchild->num_rows > 0) {
                          // output data of grandchild
                          while($row_gchild = $result_gchild->fetch_assoc()) {
                            $cmp_type = $row_gchild["cmp_type"];
                            $gc_id=$c_id."-".$gc;
                            echo "<tr data-tt-id = '".$gc_id."' data-tt-parent-id='".$c_id."'>";
                            echo "<td>".$cmp_type."</td><td></td><td></td><td></td><td></td>";
                            echo "</tr>";
                            $gc++;
                            //find great-grandchild data
                            $sql_ggchild = "SELECT * from sbom 
                                            where app_name = '".$app_name."' 
                                            and app_id = '".$app_id."' 
                                            and app_version = '".$app_version."' 
                                            and app_status = '".$app_status."'
                                            and cmp_type = '".$cmp_type."'
                                            order by app_name, cmp_type, cmp_name; ";
                                            $result_ggchild = $db->query($sql_ggchild);
                                            $ggc=1;
                                            if ($result_ggchild->num_rows > 0) {
                                              // output data of grandchild
                                              while($row_ggchild = $result_ggchild->fetch_assoc()) {
                                                $cmp_name = $row_ggchild["cmp_name"];
                                                $cmp_id = $row_ggchild["cmp_id"];
                                                $cmp_version = $row_ggchild["cmp_version"];
                                                $cmp_status = $row_ggchild["cmp_status"];
                                                $notes = $row_ggchild["notes"];
                                                $ggc_id = $gc_id."-".$ggc;
                                                echo "<tr data-tt-id = '".$ggc_id."' data-tt-parent-id='".$gc_id."'>";
                                                echo "<td>".$cmp_name."</td>";
                                                echo "<td>".$cmp_id."</td>";
                                                echo "<td>".$cmp_version."</td>";
                                                echo "<td>".$cmp_status."</td>";
                                                echo "<td>".$notes."</td>";
                                                echo "</tr>";
                                                $ggc++;
                                              } 
                                              $result_ggchild -> close();
                                            }
                                          } 
                                          $result_gchild -> close();
                                        }
                                      } 
                                      $result_child -> close();
                                    } 
                                  } 
                                  $result_parent->close();
                                }
                                else{
                                  echo "<tr data-tt-id = 'No Results'>";
                                  echo "<td>No Results Found</td><td></td><td></td><td></td><td></td>";
                                  echo "</tr>";
                                }
          ?>
        </tbody>
      </table>

  </div>
</div>
<?php include("./footer.php"); ?>
<script>
  //Params for the treetable
  let sbom_params = {
    expandable: true,
    clickableNodeNames: true
    };
    $("#bom_treetable").treetable(sbom_params);
    $("#bom_treetable tbody").on("mousedown", "tr", function() {
      $(".selected").not(this).removeClass("selected");
      $(this).toggleClass("selected");
      }); 
      </script>
