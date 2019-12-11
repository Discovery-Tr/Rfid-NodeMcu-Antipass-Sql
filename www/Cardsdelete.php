<?php
if (session_id() == "") session_start(); // Initialize Session data
ob_start(); // Turn on output buffering
?>
<?php include_once "ewcfg10.php" ?>
<?php include_once "ewmysql10.php" ?>
<?php include_once "phpfn10.php" ?>
<?php include_once "Cardsinfo.php" ?>
<?php include_once "userfn10.php" ?>
<?php

//
// Page class
//

$Cards_delete = NULL; // Initialize page object first

class cCards_delete extends cCards {

	// Page ID
	var $PageID = 'delete';

	// Project ID
	var $ProjectID = "{A465A203-2D9F-40EC-9F78-053F2F1FA743}";

	// Table name
	var $TableName = 'Cards';

	// Page object name
	var $PageObjName = 'Cards_delete';

	// Page name
	function PageName() {
		return ew_CurrentPage();
	}

	// Page URL
	function PageUrl() {
		$PageUrl = ew_CurrentPage() . "?";
		if ($this->UseTokenInUrl) $PageUrl .= "t=" . $this->TableVar . "&"; // Add page token
		return $PageUrl;
	}

	// Message
	function getMessage() {
		return @$_SESSION[EW_SESSION_MESSAGE];
	}

	function setMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_MESSAGE], $v);
	}

	function getFailureMessage() {
		return @$_SESSION[EW_SESSION_FAILURE_MESSAGE];
	}

	function setFailureMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_FAILURE_MESSAGE], $v);
	}

	function getSuccessMessage() {
		return @$_SESSION[EW_SESSION_SUCCESS_MESSAGE];
	}

	function setSuccessMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_SUCCESS_MESSAGE], $v);
	}

	function getWarningMessage() {
		return @$_SESSION[EW_SESSION_WARNING_MESSAGE];
	}

	function setWarningMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_WARNING_MESSAGE], $v);
	}

	// Show message
	function ShowMessage() {
		$hidden = FALSE;
		$html = "";

		// Message
		$sMessage = $this->getMessage();
		$this->Message_Showing($sMessage, "");
		if ($sMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sMessage;
			$html .= "<div class=\"alert alert-success ewSuccess\">" . $sMessage . "</div>";
			$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message in Session
		}

		// Warning message
		$sWarningMessage = $this->getWarningMessage();
		$this->Message_Showing($sWarningMessage, "warning");
		if ($sWarningMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sWarningMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sWarningMessage;
			$html .= "<div class=\"alert alert-warning ewWarning\">" . $sWarningMessage . "</div>";
			$_SESSION[EW_SESSION_WARNING_MESSAGE] = ""; // Clear message in Session
		}

		// Success message
		$sSuccessMessage = $this->getSuccessMessage();
		$this->Message_Showing($sSuccessMessage, "success");
		if ($sSuccessMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sSuccessMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sSuccessMessage;
			$html .= "<div class=\"alert alert-success ewSuccess\">" . $sSuccessMessage . "</div>";
			$_SESSION[EW_SESSION_SUCCESS_MESSAGE] = ""; // Clear message in Session
		}

		// Failure message
		$sErrorMessage = $this->getFailureMessage();
		$this->Message_Showing($sErrorMessage, "failure");
		if ($sErrorMessage <> "") { // Message in Session, display
			if (!$hidden)
				$sErrorMessage = "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>" . $sErrorMessage;
			$html .= "<div class=\"alert alert-error ewError\">" . $sErrorMessage . "</div>";
			$_SESSION[EW_SESSION_FAILURE_MESSAGE] = ""; // Clear message in Session
		}
		echo "<table class=\"ewStdTable\"><tr><td><div class=\"ewMessageDialog\"" . (($hidden) ? " style=\"display: none;\"" : "") . ">" . $html . "</div></td></tr></table>";
	}
	var $PageHeader;
	var $PageFooter;

	// Show Page Header
	function ShowPageHeader() {
		$sHeader = $this->PageHeader;
		$this->Page_DataRendering($sHeader);
		if ($sHeader <> "") { // Header exists, display
			echo "<p>" . $sHeader . "</p>";
		}
	}

	// Show Page Footer
	function ShowPageFooter() {
		$sFooter = $this->PageFooter;
		$this->Page_DataRendered($sFooter);
		if ($sFooter <> "") { // Footer exists, display
			echo "<p>" . $sFooter . "</p>";
		}
	}

	// Validate page request
	function IsPageRequest() {
		global $objForm;
		if ($this->UseTokenInUrl) {
			if ($objForm)
				return ($this->TableVar == $objForm->GetValue("t"));
			if (@$_GET["t"] <> "")
				return ($this->TableVar == $_GET["t"]);
		} else {
			return TRUE;
		}
	}

	//
	// Page class constructor
	//
	function __construct() {
		global $conn, $Language;
		$GLOBALS["Page"] = &$this;

		// Language object
		if (!isset($Language)) $Language = new cLanguage();

		// Parent constuctor
		parent::__construct();

		// Table object (Cards)
		if (!isset($GLOBALS["Cards"]) || get_class($GLOBALS["Cards"]) == "cCards") {
			$GLOBALS["Cards"] = &$this;
			$GLOBALS["Table"] = &$GLOBALS["Cards"];
		}

		// Page ID
		if (!defined("EW_PAGE_ID"))
			define("EW_PAGE_ID", 'delete', TRUE);

		// Table name (for backward compatibility)
		if (!defined("EW_TABLE_NAME"))
			define("EW_TABLE_NAME", 'Cards', TRUE);

		// Start timer
		if (!isset($GLOBALS["gTimer"])) $GLOBALS["gTimer"] = new cTimer();

		// Open connection
		if (!isset($conn)) $conn = ew_Connect();
	}

	// 
	//  Page_Init
	//
	function Page_Init() {
		global $gsExport, $gsExportFile, $UserProfile, $Language, $Security, $objForm;

		// Security
		$Security = new cAdvancedSecurity();
		if (!$Security->IsLoggedIn()) $Security->AutoLogin();
		if (!$Security->IsLoggedIn()) {
			$Security->SaveLastUrl();
			$this->Page_Terminate("login.php");
		}
		$this->CurrentAction = (@$_GET["a"] <> "") ? $_GET["a"] : @$_POST["a_list"]; // Set up curent action
		$this->id->Visible = !$this->IsAdd() && !$this->IsCopy() && !$this->IsGridAdd();

		// Global Page Loading event (in userfn*.php)
		Page_Loading();

		// Page Load event
		$this->Page_Load();
	}

	//
	// Page_Terminate
	//
	function Page_Terminate($url = "") {
		global $conn;

		// Page Unload event
		$this->Page_Unload();

		// Global Page Unloaded event (in userfn*.php)
		Page_Unloaded();
		$this->Page_Redirecting($url);

		 // Close connection
		$conn->Close();

		// Go to URL if specified
		if ($url <> "") {
			if (!EW_DEBUG_ENABLED && ob_get_length())
				ob_end_clean();
			header("Location: " . $url);
		}
		exit();
	}
	var $TotalRecs = 0;
	var $RecCnt;
	var $RecKeys = array();
	var $Recordset;
	var $StartRowCnt = 1;
	var $RowCnt = 0;

	//
	// Page main
	//
	function Page_Main() {
		global $Language;

		// Set up Breadcrumb
		$this->SetupBreadcrumb();

		// Load key parameters
		$this->RecKeys = $this->GetRecordKeys(); // Load record keys
		$sFilter = $this->GetKeyFilter();
		if ($sFilter == "")
			$this->Page_Terminate("Cardslist.php"); // Prevent SQL injection, return to list

		// Set up filter (SQL WHHERE clause) and get return SQL
		// SQL constructor in Cards class, Cardsinfo.php

		$this->CurrentFilter = $sFilter;

		// Get action
		if (@$_POST["a_delete"] <> "") {
			$this->CurrentAction = $_POST["a_delete"];
		} else {
			$this->CurrentAction = "I"; // Display record
		}
		switch ($this->CurrentAction) {
			case "D": // Delete
				$this->SendEmail = TRUE; // Send email on delete success
				if ($this->DeleteRows()) { // Delete rows
					if ($this->getSuccessMessage() == "")
						$this->setSuccessMessage($Language->Phrase("DeleteSuccess")); // Set up success message
					$this->Page_Terminate($this->getReturnUrl()); // Return to caller
				}
		}
	}

// No functions
	// Load recordset
	function LoadRecordset($offset = -1, $rowcnt = -1) {
		global $conn;

		// Call Recordset Selecting event
		$this->Recordset_Selecting($this->CurrentFilter);

		// Load List page SQL
		$sSql = $this->SelectSQL();
		if ($offset > -1 && $rowcnt > -1)
			$sSql .= " LIMIT $rowcnt OFFSET $offset";

		// Load recordset
		$rs = ew_LoadRecordset($sSql);

		// Call Recordset Selected event
		$this->Recordset_Selected($rs);
		return $rs;
	}

	// Load row based on key values
	function LoadRow() {
		global $conn, $Security, $Language;
		$sFilter = $this->KeyFilter();

		// Call Row Selecting event
		$this->Row_Selecting($sFilter);

		// Load SQL based on filter
		$this->CurrentFilter = $sFilter;
		$sSql = $this->SQL();
		$res = FALSE;
		$rs = ew_LoadRecordset($sSql);
		if ($rs && !$rs->EOF) {
			$res = TRUE;
			$this->LoadRowValues($rs); // Load row values
			$rs->Close();
		}
		return $res;
	}

	// Load row values from recordset
	function LoadRowValues(&$rs) {
		global $conn;
		if (!$rs || $rs->EOF) return;

		// Call Row Selected event
		$row = &$rs->fields;
		$this->Row_Selected($row);
		$this->id->setDbValue($rs->fields('id'));
		$this->CardId->setDbValue($rs->fields('CardId'));
		$this->Name->setDbValue($rs->fields('Name'));
		$this->Surname->setDbValue($rs->fields('Surname'));
		$this->Phone->setDbValue($rs->fields('Phone'));
		$this->Active->setDbValue($rs->fields('Active'));
	}

	// Load DbValue from recordset
	function LoadDbValues(&$rs) {
		if (!$rs || !is_array($rs) && $rs->EOF) return;
		$row = is_array($rs) ? $rs : $rs->fields;
		$this->id->DbValue = $row['id'];
		$this->CardId->DbValue = $row['CardId'];
		$this->Name->DbValue = $row['Name'];
		$this->Surname->DbValue = $row['Surname'];
		$this->Phone->DbValue = $row['Phone'];
		$this->Active->DbValue = $row['Active'];
	}

	// Render row values based on field settings
	function RenderRow() {
		global $conn, $Security, $Language;
		global $gsLanguage;

		// Initialize URLs
		// Call Row_Rendering event

		$this->Row_Rendering();

		// Common render codes for all row types
		// id
		// CardId
		// Name
		// Surname
		// Phone
		// Active

		if ($this->RowType == EW_ROWTYPE_VIEW) { // View row

			// id
			$this->id->ViewValue = $this->id->CurrentValue;
			$this->id->ViewCustomAttributes = "";

			// CardId
			$this->CardId->ViewValue = $this->CardId->CurrentValue;
			$this->CardId->ViewCustomAttributes = "";

			// Name
			$this->Name->ViewValue = $this->Name->CurrentValue;
			$this->Name->ViewCustomAttributes = "";

			// Surname
			$this->Surname->ViewValue = $this->Surname->CurrentValue;
			$this->Surname->ViewCustomAttributes = "";

			// Phone
			$this->Phone->ViewValue = $this->Phone->CurrentValue;
			$this->Phone->ViewCustomAttributes = "";

			// Active
			$this->Active->ViewValue = $this->Active->CurrentValue;
			$this->Active->ViewCustomAttributes = "";

			// id
			$this->id->LinkCustomAttributes = "";
			$this->id->HrefValue = "";
			$this->id->TooltipValue = "";

			// CardId
			$this->CardId->LinkCustomAttributes = "";
			$this->CardId->HrefValue = "";
			$this->CardId->TooltipValue = "";

			// Name
			$this->Name->LinkCustomAttributes = "";
			$this->Name->HrefValue = "";
			$this->Name->TooltipValue = "";

			// Surname
			$this->Surname->LinkCustomAttributes = "";
			$this->Surname->HrefValue = "";
			$this->Surname->TooltipValue = "";

			// Phone
			$this->Phone->LinkCustomAttributes = "";
			$this->Phone->HrefValue = "";
			$this->Phone->TooltipValue = "";

			// Active
			$this->Active->LinkCustomAttributes = "";
			$this->Active->HrefValue = "";
			$this->Active->TooltipValue = "";
		}

		// Call Row Rendered event
		if ($this->RowType <> EW_ROWTYPE_AGGREGATEINIT)
			$this->Row_Rendered();
	}

	//
	// Delete records based on current filter
	//
	function DeleteRows() {
		global $conn, $Language, $Security;
		$DeleteRows = TRUE;
		$sSql = $this->SQL();
		$conn->raiseErrorFn = 'ew_ErrorFn';
		$rs = $conn->Execute($sSql);
		$conn->raiseErrorFn = '';
		if ($rs === FALSE) {
			return FALSE;
		} elseif ($rs->EOF) {
			$this->setFailureMessage($Language->Phrase("NoRecord")); // No record found
			$rs->Close();
			return FALSE;

		//} else {
		//	$this->LoadRowValues($rs); // Load row values

		}
		$conn->BeginTrans();

		// Clone old rows
		$rsold = ($rs) ? $rs->GetRows() : array();
		if ($rs)
			$rs->Close();

		// Call row deleting event
		if ($DeleteRows) {
			foreach ($rsold as $row) {
				$DeleteRows = $this->Row_Deleting($row);
				if (!$DeleteRows) break;
			}
		}
		if ($DeleteRows) {
			$sKey = "";
			foreach ($rsold as $row) {
				$sThisKey = "";
				if ($sThisKey <> "") $sThisKey .= $GLOBALS["EW_COMPOSITE_KEY_SEPARATOR"];
				$sThisKey .= $row['id'];
				$conn->raiseErrorFn = 'ew_ErrorFn';
				$DeleteRows = $this->Delete($row); // Delete
				$conn->raiseErrorFn = '';
				if ($DeleteRows === FALSE)
					break;
				if ($sKey <> "") $sKey .= ", ";
				$sKey .= $sThisKey;
			}
		} else {

			// Set up error message
			if ($this->getSuccessMessage() <> "" || $this->getFailureMessage() <> "") {

				// Use the message, do nothing
			} elseif ($this->CancelMessage <> "") {
				$this->setFailureMessage($this->CancelMessage);
				$this->CancelMessage = "";
			} else {
				$this->setFailureMessage($Language->Phrase("DeleteCancelled"));
			}
		}
		if ($DeleteRows) {
			$conn->CommitTrans(); // Commit the changes
		} else {
			$conn->RollbackTrans(); // Rollback changes
		}

		// Call Row Deleted event
		if ($DeleteRows) {
			foreach ($rsold as $row) {
				$this->Row_Deleted($row);
			}
		}
		return $DeleteRows;
	}

	// Set up Breadcrumb
	function SetupBreadcrumb() {
		global $Breadcrumb, $Language;
		$Breadcrumb = new cBreadcrumb();
		$Breadcrumb->Add("list", $this->TableVar, "Cardslist.php", $this->TableVar, TRUE);
		$PageId = "delete";
		$Breadcrumb->Add("delete", $PageId, ew_CurrentUrl());
	}

	// Page Load event
	function Page_Load() {

		//echo "Page Load";
	}

	// Page Unload event
	function Page_Unload() {

		//echo "Page Unload";
	}

	// Page Redirecting event
	function Page_Redirecting(&$url) {

		// Example:
		//$url = "your URL";

	}

	// Message Showing event
	// $type = ''|'success'|'failure'|'warning'
	function Message_Showing(&$msg, $type) {
		if ($type == 'success') {

			//$msg = "your success message";
		} elseif ($type == 'failure') {

			//$msg = "your failure message";
		} elseif ($type == 'warning') {

			//$msg = "your warning message";
		} else {

			//$msg = "your message";
		}
	}

	// Page Render event
	function Page_Render() {

		//echo "Page Render";
	}

	// Page Data Rendering event
	function Page_DataRendering(&$header) {

		// Example:
		//$header = "your header";

	}

	// Page Data Rendered event
	function Page_DataRendered(&$footer) {

		// Example:
		//$footer = "your footer";

	}
}
?>
<?php ew_Header(FALSE) ?>
<?php

// Create page object
if (!isset($Cards_delete)) $Cards_delete = new cCards_delete();

// Page init
$Cards_delete->Page_Init();

// Page main
$Cards_delete->Page_Main();

// Global Page Rendering event (in userfn*.php)
Page_Rendering();

// Page Rendering event
$Cards_delete->Page_Render();
?>
<?php include_once "header.php" ?>
<script type="text/javascript">

// Page object
var Cards_delete = new ew_Page("Cards_delete");
Cards_delete.PageID = "delete"; // Page ID
var EW_PAGE_ID = Cards_delete.PageID; // For backward compatibility

// Form object
var fCardsdelete = new ew_Form("fCardsdelete");

// Form_CustomValidate event
fCardsdelete.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }

// Use JavaScript validation or not
<?php if (EW_CLIENT_VALIDATE) { ?>
fCardsdelete.ValidateRequired = true;
<?php } else { ?>
fCardsdelete.ValidateRequired = false; 
<?php } ?>

// Dynamic selection lists
// Form object for search

</script>
<script type="text/javascript">

// Write your client script here, no need to add script tags.
</script>
<?php

// Load records for display
if ($Cards_delete->Recordset = $Cards_delete->LoadRecordset())
	$Cards_deleteTotalRecs = $Cards_delete->Recordset->RecordCount(); // Get record count
if ($Cards_deleteTotalRecs <= 0) { // No record found, exit
	if ($Cards_delete->Recordset)
		$Cards_delete->Recordset->Close();
	$Cards_delete->Page_Terminate("Cardslist.php"); // Return to list
}
?>
<?php $Breadcrumb->Render(); ?>
<?php $Cards_delete->ShowPageHeader(); ?>
<?php
$Cards_delete->ShowMessage();
?>
<form name="fCardsdelete" id="fCardsdelete" class="ewForm form-horizontal" action="<?php echo ew_CurrentPage() ?>" method="post">
<input type="hidden" name="t" value="Cards">
<input type="hidden" name="a_delete" id="a_delete" value="D">
<?php foreach ($Cards_delete->RecKeys as $key) { ?>
<?php $keyvalue = is_array($key) ? implode($EW_COMPOSITE_KEY_SEPARATOR, $key) : $key; ?>
<input type="hidden" name="key_m[]" value="<?php echo ew_HtmlEncode($keyvalue) ?>">
<?php } ?>
<table class="ewGrid"><tr><td class="ewGridContent">
<div class="ewGridMiddlePanel">
<table id="tbl_Cardsdelete" class="ewTable ewTableSeparate">
<?php echo $Cards->TableCustomInnerHtml ?>
	<thead>
	<tr class="ewTableHeader">
<?php if ($Cards->id->Visible) { // id ?>
		<td><span id="elh_Cards_id" class="Cards_id"><?php echo $Cards->id->FldCaption() ?></span></td>
<?php } ?>
<?php if ($Cards->CardId->Visible) { // CardId ?>
		<td><span id="elh_Cards_CardId" class="Cards_CardId"><?php echo $Cards->CardId->FldCaption() ?></span></td>
<?php } ?>
<?php if ($Cards->Name->Visible) { // Name ?>
		<td><span id="elh_Cards_Name" class="Cards_Name"><?php echo $Cards->Name->FldCaption() ?></span></td>
<?php } ?>
<?php if ($Cards->Surname->Visible) { // Surname ?>
		<td><span id="elh_Cards_Surname" class="Cards_Surname"><?php echo $Cards->Surname->FldCaption() ?></span></td>
<?php } ?>
<?php if ($Cards->Phone->Visible) { // Phone ?>
		<td><span id="elh_Cards_Phone" class="Cards_Phone"><?php echo $Cards->Phone->FldCaption() ?></span></td>
<?php } ?>
<?php if ($Cards->Active->Visible) { // Active ?>
		<td><span id="elh_Cards_Active" class="Cards_Active"><?php echo $Cards->Active->FldCaption() ?></span></td>
<?php } ?>
	</tr>
	</thead>
	<tbody>
<?php
$Cards_delete->RecCnt = 0;
$i = 0;
while (!$Cards_delete->Recordset->EOF) {
	$Cards_delete->RecCnt++;
	$Cards_delete->RowCnt++;

	// Set row properties
	$Cards->ResetAttrs();
	$Cards->RowType = EW_ROWTYPE_VIEW; // View

	// Get the field contents
	$Cards_delete->LoadRowValues($Cards_delete->Recordset);

	// Render row
	$Cards_delete->RenderRow();
?>
	<tr<?php echo $Cards->RowAttributes() ?>>
<?php if ($Cards->id->Visible) { // id ?>
		<td<?php echo $Cards->id->CellAttributes() ?>>
<span id="el<?php echo $Cards_delete->RowCnt ?>_Cards_id" class="control-group Cards_id">
<span<?php echo $Cards->id->ViewAttributes() ?>>
<?php echo $Cards->id->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Cards->CardId->Visible) { // CardId ?>
		<td<?php echo $Cards->CardId->CellAttributes() ?>>
<span id="el<?php echo $Cards_delete->RowCnt ?>_Cards_CardId" class="control-group Cards_CardId">
<span<?php echo $Cards->CardId->ViewAttributes() ?>>
<?php echo $Cards->CardId->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Cards->Name->Visible) { // Name ?>
		<td<?php echo $Cards->Name->CellAttributes() ?>>
<span id="el<?php echo $Cards_delete->RowCnt ?>_Cards_Name" class="control-group Cards_Name">
<span<?php echo $Cards->Name->ViewAttributes() ?>>
<?php echo $Cards->Name->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Cards->Surname->Visible) { // Surname ?>
		<td<?php echo $Cards->Surname->CellAttributes() ?>>
<span id="el<?php echo $Cards_delete->RowCnt ?>_Cards_Surname" class="control-group Cards_Surname">
<span<?php echo $Cards->Surname->ViewAttributes() ?>>
<?php echo $Cards->Surname->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Cards->Phone->Visible) { // Phone ?>
		<td<?php echo $Cards->Phone->CellAttributes() ?>>
<span id="el<?php echo $Cards_delete->RowCnt ?>_Cards_Phone" class="control-group Cards_Phone">
<span<?php echo $Cards->Phone->ViewAttributes() ?>>
<?php echo $Cards->Phone->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
<?php if ($Cards->Active->Visible) { // Active ?>
		<td<?php echo $Cards->Active->CellAttributes() ?>>
<span id="el<?php echo $Cards_delete->RowCnt ?>_Cards_Active" class="control-group Cards_Active">
<span<?php echo $Cards->Active->ViewAttributes() ?>>
<?php echo $Cards->Active->ListViewValue() ?></span>
</span>
</td>
<?php } ?>
	</tr>
<?php
	$Cards_delete->Recordset->MoveNext();
}
$Cards_delete->Recordset->Close();
?>
</tbody>
</table>
</div>
</td></tr></table>
<div class="btn-group ewButtonGroup">
<button class="btn btn-primary ewButton" name="btnAction" id="btnAction" type="submit"><?php echo $Language->Phrase("DeleteBtn") ?></button>
</div>
</form>
<script type="text/javascript">
fCardsdelete.Init();
</script>
<?php
$Cards_delete->ShowPageFooter();
if (EW_DEBUG_ENABLED)
	echo ew_DebugMsg();
?>
<script type="text/javascript">

// Write your table-specific startup script here
// document.write("page loaded");

</script>
<?php include_once "footer.php" ?>
<?php
$Cards_delete->Page_Terminate();
?>
