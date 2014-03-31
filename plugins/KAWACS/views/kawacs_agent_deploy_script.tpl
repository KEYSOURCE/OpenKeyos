{literal}
'Settings first
'these settings should be set by the server
{/literal}
const KS_CUSTOMER_ID = {$script_customer_id}
const KS_SERVER_URL =  "https://{$script_server_url}"
const KS_COMPUTER_TYPE =  {$script_computer_type}
const KS_COMPUTER_PROFILE = {$script_monitor_profile}
const KS_REPORT_INTERVAL = {$script_report_interval}
const strKADeployUrl = "http://{$smarty.const.BASE_URL}/files/agent_installer/KADeploy.exe"
const strAgentUrl = "http://{$smarty.const.BASE_URL}/files/agent_installer/setup.exe"
const strAgentIni = "http://{$smarty.const.BASE_URL}/files/agent_installer/setup.ini"
'const strUrl = '"{$installer_url}"
'const zipLibUrl = "{$smarty.const.BASE_URL}/XZip.dll"
'{literal}
'here begins the normal script execution
const KEY_QUERY_VALUE = &H0001
const KEY_SET_VALUE = &H0002
const KEY_CREATE_SUB_KEY = &H0004

const HKEY_LOCAL_MACHINE = &H80000002

function downloadFile(fileURL, dest_file)
    set objHTTP = CreateObject("MSXML2.XMLHTTP")
    objHTTP.open "GET", fileURL, false
    objHTTP.send()
    
    if objHTTP.Status = 200 then
        set adoStream = CreateObject("ADODB.Stream")
        adoStream.Open
        adoStream.Type = 1
        adoStream.Write objHTTP.ResponseBody
        adoStream.Position = 0
        
        set fso = CreateObject("Scripting.FileSystemObject")
        sCurPath = fso.GetAbsolutePathName(".")
        dest_file = sCurPath & "\" & dest_file
        if fso.FileExists(dest_file) then
            fso.DeleteFile(dest_file)
        end if
        set fso = Nothing
        adoStream.SaveToFile dest_file
        adoStream.Close
        set adoStream = Nothing
    end if
    set objHTTP = Nothing
end function

set objShell = CreateObject("WScript.Shell")
strDestFile = "kawacs_agent_installer.zip"
strExtractTo = "kawcs_agent_installer"


'fetch the file
'downloadFile zipLibUrl, "XZip.dll"
'iRC = objShell.Run("regsvr32 XZip.dll /silent /verysilent /supressmsgboxes /SMS /x", 1, True)
'Wscript.Sleep 15000

set objFSO = CreateObject("Scripting.FileSystemObject")
sCurPath = objFSO.GetAbsolutePathName(".")

if objFSO.FolderExists(strExtractTo) then
    set ff = objFSO.GetFolder(strExtractTo)
    ff.Delete True
end if

set objFldr = objFSO.CreateFolder(strExtractTo)

downloadFile strAgentIni, strExtractTo & "\setup.ini"
downloadFile strAgentUrl, strExtractTo & "\setup.exe"
downloadFile strKADeployUrl, strExtractTo & "\KADeploy.exe"

strExtractTo = sCurPath & "\" & strExtractTo

' the installer is downloaded, now make shure the correct values are entered in the
' kawacs agent registry settings
' first make shure we have enough rights

dim oReg
strComputer = "."
set oReg = GetObject("winmgmts:{impersonationLevel=impersonate}!\\" & strComputer & "\root\default:StdRegProv")
strKeyPath = "SOFTWARE"

function keyExists(strKey)
    v = oReg.EnumValues(HKEY_LOCAL_MACHINE, strKey, arrValNames, arrValTypes)
    if v <> 0 then
        keyExists = false
    else
        keyExists = true
    end if
end function

oReg.CheckAccess HKEY_LOCAL_MACHINE, strKeyPath, KEY_QUERY_VALUE, bHasQueryAccessRight
oReg.CheckAccess HKEY_LOCAL_MACHINE, strKeyPath, KEY_SET_VALUE, bHasSetAccessRight
oReg.CheckAccess HKEY_LOCAL_MACHINE, strKeyPath, KEY_CREATE_SUB_KEY, bHasCSKAccessRight

strKSKey = "SOFTWARE\Keysource"
strK = "\KawacsAgent"

if bHasCSKAccessRight then
    if not keyExists(strKSKey) then
        oReg.CreateKey HKEY_LOCAL_MACHINE, strKSKey
    end if
    if keyExists(strKSKey) then
        oReg.CreateKey HKEY_LOCAL_MACHINE, strKSKey & strK
    end if
end if
strKSKey = strKSKey & strK
if keyExists(strKSKey) and (bHasSetAccessRight = True) then
    oReg.setStringValue HKEY_LOCAL_MACHINE, strKSKey, "ServerURL", KS_SERVER_URL
    oReg.setDWORDValue HKEY_LOCAL_MACHINE, strKSKey, "CustomerID", KS_CUSTOMER_ID
    oReg.setDWORDValue HKEY_LOCAL_MACHINE, strKSKey, "Type", KS_COMPUTER_TYPE
    oReg.setDWORDValue HKEY_LOCAL_MACHINE, strKSKey, "Profile", KS_COMPUTER_PROFILE
    oReg.setDWORDValue HKEY_LOCAL_MACHINE, strKSKey, "ReportInterval", KS_REPORT_INTERVAL
end if

set oReg = Nothing

function extractZIP(filename, extract_to)
    set fso = CreateObject("Scripting.FileSystemObject")
    if fso.FileExists(filename) then
        if not fso.FolderExists(extract_to) then
            fso.CreateFolder(extract_to)
        end if
    end if
    
    set objZIP = CreateObject("XStandard.Zip")
    objZIP.UnPack filename, extract_to
    set objZIP = Nothing
end function 

function extractZIPSys(filename, extract_to)
    set fso = CreateObject("Scripting.FileSystemObject")
    if fso.FileExists(filename) then
        'WScript.Echo(extract_to)
        if not fso.FolderExists(extract_to) then
            fso.CreateFolder(extract_to)
        end if

        set objShellx = CreateObject("Shell.Application")
        set FilesInZip = objShellx.NameSpace(filename).items
        objShellx.NameSpace(extract_to).CopyHere FilesInZip,4
        set objShellx = Nothing
    end if
    set fso = Nothing
end function

'now run the program
' first add the parameters to the command
' practically I have only one parameter that needs to be set the customerID
' but this can be extended to something like computer type and computer profile
' reporting type etc
' the only thig is to succeed in passing argumetns to the delphi installer

if CreateObject("Scripting.FileSystemObject").FileExists(strDestFile) then
    'objShell.Run(strDestFile & " " & KS_CUSTOMER_ID)
    'if iRC = 0 then
    '    extractZIP strDestFile, strExtractTo
    'else
    '    'register failed.... try the system unpacker
    '    extractZIPSys strDestFile, strExtractTo
    'end if
    
    Wscript.Sleep 5000
    deployer = strExtractTo & "\KADeploy.exe"
    objShell.Run(deployer & " /customer_id:" & KS_CUSTOMER_ID & " /server_url:" & KS_SERVER_URL & " /monitor_profile:" & KS_COMPUTER_PROFILE & " /computer_type:" & KS_COMPUTER_TYPE & " /report_interval:" & KS_REPORT_INTERVAL & " /silent /verysilent /supressmsgboxes /SMS /x")
end if
'now destroy the shell object
set objShell = Nothing
'finally quit
Wscript.Quit


{/literal}