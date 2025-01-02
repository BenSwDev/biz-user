# ---------- aggregate-files.ps1 ----------

# Define the JSON with the list of files
$jsonString = @"
{
  "files": [
    "partials/menu.php",
    "partials/menu_member.php",
    "partials/orders_menu.php",
    "partials/submenu.php",
    "partials/settings_menu.php"
   ]
}
"@

# Convert the JSON string to a PowerShell object
$data = $jsonString | ConvertFrom-Json
$filesList = $data.files

# Define the output file name (can be changed if needed)
$outputFile = Join-Path $PSScriptRoot "aggregated-files.txt"

# If an output file with the same name already exists, remove it to create a new file
if (Test-Path $outputFile) {
    Remove-Item $outputFile
}

# Loop through the list of files defined in the JSON
foreach ($file in $filesList) {

    # Build the full path to the file. Assumes that the script is located in the project's root directory
    $fullPath = Join-Path $PSScriptRoot $file

    # Check if the file exists
    if (-not (Test-Path $fullPath)) {
        Write-Host "File '$file' not found at path: $fullPath - skipping..."
        continue
    }

    # Read the file content (in "Raw" mode to get the entire content as a single string)
    $content = Get-Content -Path $fullPath -Raw

    # Build the content to write to the output file
    # Add an empty line after each file for readability
    $output = @()
    $output += "file name: /$file"
    $output += "file content:"
    $output += $content
    $output += "--- end file name ---"
    $output += ""

    # Write to the output file (in Append mode so as not to overwrite previous content)
    $output | Out-File -FilePath $outputFile -Append -Encoding UTF8
}

Write-Host "The file 'aggregated-files.txt' was successfully created/updated in the root directory."
# ---------- end of aggregate-files.ps1 ----------
