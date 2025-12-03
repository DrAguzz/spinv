<?php 
  $nav = "../";
  $link = "../include/";
  include($link."container/head.php");
  include($link."container/nav.php");
?>
  <!-- Main Content -->
  <div class="main">
    <!-- Header top: marble, agent, request details -->
    <div class="header-top">
      <div class="marble-details">
        <h3>Marble Detail</h3>
        <table>
          <tr>
            <td>Marble Name</td>
            <td>: Marble A</td>
          </tr>
          <tr>
            <td>Marble Category</td>
            <td>: Category A</td>
          </tr>
          <tr>
            <td>Marble Finishes</td>
            <td>: Polished</td>
          </tr>
          <tr>
            <td>Cut Area (m²)</td>
            <td>: 9809</td>
          </tr>
          <tr>
            <td>Balance Area</td>
            <td>: </td>
          </tr>
          <tr>
            <td>Price Out</td>
            <td>: </td>
          </tr>
        </table>
      </div>
      <div class="request-details">
        <h3>Request Detail</h3>
        <table>
          <tr>
            <td>Request ID</td>
            <td>: RQ123</td>
          </tr>
          <tr>
            <td>Request Date</td>
            <td>: 2023-10-01</td>
          </tr>
          <tr>
            <td>Status</td>
            <td>: Pending</td>
          </tr>
        </table>
      </div>
      <div class="agent-details">
        <h3>Agent Detail</h3>
        <table>
          <tr>
            <td>Agent ID</td>
            <td>: SP011</td>
          </tr>
          <tr>
            <td>Agent Name</td>
            <td>: </td>
          </tr>
        </table>
      </div>
      
    </div>

    <!-- Stock In Progress Table -->
    <div class="stock-section">
      <h2>Stock In Progress</h2>
      <div class="search-box">
        <input type="text" placeholder="Search..." />
      </div>
      <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Marble-ID</th>
          <th>Agent-ID</th>
          <th>Edit/Added</th>
          <th>Detail</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>RQ110</td>
          <td>A001</td>
          <td>SP011</td>
          <td>Edit</td>
          <td><a href="./detailSIP.html"><button class="btn btn-primary">Detail</button></a></td>
          <td>
            <button class="btn btn-secondary">Reject</button>
            <button class="btn btn-main">Approve</button>
          </td>
        </tr>
        <tr>
          <td>RQ111</td>
          <td>M011</td>
          <td>SP021</td>
          <td>Added</td>
          <td><a href="./detailSIP.html"><button class="btn btn-primary">Detail</button></a></td>
          <td>
            <button class="btn btn-secondary">Reject</button>
            <button class="btn btn-main">Approve</button>
          </td>
        </tr>
      </tbody>
    </table>
    </div>
  </div>
<?php 
  include($link."container/footer.php");
?>