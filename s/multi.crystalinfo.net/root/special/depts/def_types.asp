<!--#include virtual="/script/functions.asp" -->
<%
  cc_page_meta()
  javascripts()
  if GetRight("")="0" Then
    call error_page("Bu sayfayý görmeye yetkili deðilsiniz! ", err_type) 
  End if
  sqlDefs="Select * From TbDefinitionTypes Where BoPassive=0"
  set rsDefs = runSqlReturnRS(session("conn"), sqlDefs, "")
%>
<br><center>
<%
  call tableHeader("Tanýmlamalar", "300")
%>

  <table width="100%" cellpadding="0" cellspacing="0" border="0">
  <%Do while not rsDefs.EOF%>
    <tr>
      <td><a href="/admin/definitions/definitions.asp?id=<%=rsDefs("InDefinitionTypeId")%>" class="link">
        <img src="<%=IMAGE_ROOT%>ok2.gif" border="0">
        <%=rsDefs("StDefinitionType")%>
      </a>
      </td>
    </tr>
  <%rsDefs.MoveNext
  Loop%>
  </table>
<%
  call tableFooter()
%>
</center>
