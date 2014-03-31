import java.io.File;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.FileWriter;
import java.io.PrintWriter;
import java.net.URL;
import java.net.MalformedURLException;
import java.util.Iterator;
import java.util.HashMap;

import net.percederberg.mibble.Mib;
import net.percederberg.mibble.MibLoader;
import net.percederberg.mibble.MibSymbol;
import net.percederberg.mibble.MibValue;
import net.percederberg.mibble.MibValueSymbol;
import net.percederberg.mibble.MibType;
import net.percederberg.mibble.MibTypeTag;
import net.percederberg.mibble.MibTypeSymbol;
import net.percederberg.mibble.MibLoaderException;
import net.percederberg.mibble.snmp.SnmpType;
import net.percederberg.mibble.value.ObjectIdentifierValue;
import net.percederberg.mibble.snmp.SnmpObjectIdentity;
import net.percederberg.mibble.snmp.SnmpObjectType;

public class MibParser
{
	public static final int NODE_TYPE_NONE 		= 0;
	public static final int NODE_TYPE_SCALAR 	= 1;
	public static final int NODE_TYPE_TABLE 	= 2;
	public static final int NODE_TYPE_TABLE_ROW 	= 4;
	public static final int NODE_TYPE_TABLE_COL 	= 8;
	
	public static String outFileName = "";
	public static FileWriter outFile = null;
	public static PrintWriter out = null;
	
	public static void main(String[] args)
	{
		MibLoader  loader = new MibLoader();
		File       file = null;
		Mib        mib = null;
		Mib[]      allMibs;
		HashMap    map = new HashMap();
		Iterator   iter = null, mibImportsIter = null;
		MibSymbol  symbol;
		MibValueSymbol	valsymbol, parent;
		MibValue   value;
		MibType		type;
		MibTypeTag	tag;
		int		nodeType, i;
		ObjectIdentifierValue  root = null;
		String		mainMibName = null;
		
		if (args.length < 1)
		{
			System.err.println ("No MIB specified");
			System.exit(1);
		}
		if (args.length > 1)
		{
			try
			{
				outFileName = args[1];
				outFile = new FileWriter (outFileName);
				out = new PrintWriter (outFile);
			}
			catch (IOException e)
			{
				System.err.println ("Failed opening output file for writing: "+args[1]);
				System.exit (1);
			}
		}
		
		// Initialize the MIB processor and attempt to load the file
		try
		{
			file = new File(args[0]);
			loader.addDir(file.getParentFile());
			//loader.addResourceDir (file.getParentFile().getAbsolutePath());
			mib = loader.load(file);
			mainMibName = mib.getName();
		}
		catch (FileNotFoundException e)
		{
			System.err.println ("File not found: "+ args[0]);
			System.exit(1);
		}
		catch (IOException e)
		{
			System.err.println ("Failed opening file: "+ args[0]);
			System.exit (1);
		}
		catch (MibLoaderException e)
		{
			e.getLog().printTo(System.err);
			System.exit(1);
		}
		catch (RuntimeException e)
		{
			System.err.println("Internal error");
			e.printStackTrace();
			System.exit(1);
		}
		
		// If we got here, the MIB parser is properly initialized, so output the processed MIB
		outputln ("<?xml version=\"1.0\"?>");
		outputln ("<mib name=\""+mib.getName()+"\">");
		if (mib.getFooterComment()!=null || mib.getHeaderComment()!=null)
		{
			outputln ("<comments>");
			if (mib.getHeaderComment()!=null) outputln (escapeHTML(mib.getHeaderComment()));
			if (mib.getFooterComment()!=null) outputln (escapeHTML(mib.getFooterComment()));
			outputln ("</comments>");
		}
		else outputln ("<comments/>");
		
		iter = mib.getAllSymbols().iterator();
		while (root == null && iter.hasNext())
		{
			symbol = (MibSymbol) iter.next();
			if (symbol instanceof MibValueSymbol)
			{
				value = ((MibValueSymbol) symbol).getValue(); // Can use this as start value in output?
				//System.out.println ("XXXX: "+value);
				if (value instanceof ObjectIdentifierValue)
				{
					root = (ObjectIdentifierValue) value;
				}
			}
		}
		
		if (root == null)
		{
			System.err.println ("no OID value could be found in " + mib.getName());
			System.exit (1);
		}
		else
		{
			//root = loader.getRootOid ();
			while (root.getParent() != null) root = root.getParent();
			
			// Start the processing only when we have more than one child
			while (root.getChildCount() == 1) root = root.getChild(0);
			
			//processOid ((MibSymbol) root.getSymbol());
			outputln ("<oids>");
			processOid (root);
			outputln ("</oids>");
		}
		outputln ("</mib>");
		
		if (outFileName != "") out.close ();
	}
	
	public static void processOid (ObjectIdentifierValue oid)
	{
		MibSymbol  		symbol;
		MibValueSymbol		valsymbol, parent;
		MibValue   		value;
		int			i, nodeType, childCount;
		MibType			type;
		MibTypeTag		tag;
		
		symbol = (MibSymbol) oid.getSymbol();
		//value = extractOid(symbol);
		//outputln ("<node oid=\""+value+"\" name=\""+symbol.getName()+"\"");
		
		//symbol = (MibSymbol) oid.getSymbol();
		value = extractOid(symbol);
		nodeType = NODE_TYPE_NONE;
		if (value != null)// && symbol instanceof MibValueSymbol)
		{
			valsymbol = (MibValueSymbol) symbol;
			childCount = valsymbol.getChildCount();
				
			if (valsymbol.isScalar()) nodeType+= NODE_TYPE_SCALAR;
			if (valsymbol.isTable()) nodeType+= NODE_TYPE_TABLE;
			if (valsymbol.isTableRow()) nodeType+= NODE_TYPE_TABLE_ROW;
			if (valsymbol.isTableColumn()) nodeType+= NODE_TYPE_TABLE_COL;
			
			if (nodeType != NODE_TYPE_NONE || childCount > 0 || valsymbol.getType() != null)
			{
				output ("<node oid=\""+value+"\" name=\""+symbol.getName()+"\" node_type=\""+nodeType+"\" ");
				output (" children_count=\""+((MibValueSymbol)symbol).getChildCount()+"\"");
				parent = (valsymbol).getParent ();
				if (parent != null) output (" parent=\""+((ObjectIdentifierValue)parent.getValue())+"\"");
			
				outputln (">");
			
//			if (symbol instanceof MibValueSymbol)
//			{
				valsymbol = (MibValueSymbol) symbol;
				type = valsymbol.getType();
				if (type != null && type instanceof SnmpObjectType)
				{
					outputln ("  <access>" + ((SnmpObjectType)type).getAccess() + "</access>");
					outputln ("  <status>" + ((SnmpObjectType)type).getStatus() + "</status>");
					outputln ("  <syntax>" + ((SnmpObjectType)type).getSyntax() + "</syntax>");
					outputln ("  <data_type>" + ((SnmpObjectType)type).getSyntax().getName() + "</data_type>");
					//outputln ("  - Extended OID: "+ ((ObjectIdentifierValue)value).toDetailString());
					outputln ("  <description>" + escapeHTML(((SnmpType)type).getDescription()) + "</description>");
				}
				outputln ("</node>");
			}
		}
		
		// Now process the children recursively too
		childCount = oid.getChildCount();
		for (i = 0; i < childCount; i++) processOid(oid.getChild(i));
	}
	
	
	public static ObjectIdentifierValue extractOid(MibSymbol symbol)
	{
		MibValue  value;
		
		if (symbol instanceof MibValueSymbol)
		{
			value = ((MibValueSymbol) symbol).getValue();
			if (value instanceof ObjectIdentifierValue)
			{
				return (ObjectIdentifierValue) value;
			}
		}
		return null;
	}
	
	public static void output (String s)
	{
		if (outFileName == "") System.out.print (s);
		else
		{
			out.print (s);
			if (out.checkError())
			{
				System.err.println ("Failed writing to output file");
				System.exit (1);
			}
		}
	}
	
	public static void outputln (String s)
	{
		output (s + "\n");
	}
	
	public static String escapeHTML (String s)
	{
		return s.replaceAll ("<", "&lt;");
	}
	
	/*
		allMibs = loader.getAllMibs();
		for (i=0; i<1; i++)
		{
		iter = allMibs[i].getAllSymbols().iterator();
		while (iter.hasNext())
		{
			symbol = (MibSymbol) iter.next();
			value = extractOid(symbol);
			nodeType = NODE_TYPE_NONE;
			if (value != null)
			{
				output ("<node oid=\""+value+"\" name=\""+symbol.getName()+"\"");
				
				if (symbol instanceof MibValueSymbol)
				{
					valsymbol = (MibValueSymbol) symbol;
					
					if (valsymbol.isScalar()) nodeType+= NODE_TYPE_SCALAR;
					if (valsymbol.isTable()) nodeType+= NODE_TYPE_TABLE;
					if (valsymbol.isTableRow()) nodeType+= NODE_TYPE_TABLE_ROW;
					if (valsymbol.isTableColumn()) nodeType+= NODE_TYPE_TABLE_COL;
					output (" node_type=\""+nodeType+"\"");
					
					parent = (valsymbol).getParent ();
					if (parent != null)
					{
						output (" parent=\""+((ObjectIdentifierValue)parent.getValue())+"\"");
						output (" children_count=\""+((MibValueSymbol)symbol).getChildCount()+"\"");
					}
				}
				outputln (">");
				
				if (symbol instanceof MibValueSymbol)
				{
					valsymbol = (MibValueSymbol) symbol;
					type = valsymbol.getType();
					if (type != null && type instanceof SnmpObjectType)
					{
						outputln ("  <access>" + ((SnmpObjectType)type).getAccess() + "</access>");
						outputln ("  <status>" + ((SnmpObjectType)type).getStatus() + "</status>");
						outputln ("  <syntax>" + ((SnmpObjectType)type).getSyntax() + "</syntax>");
						outputln ("  <data_type>" + ((SnmpObjectType)type).getSyntax().getName() + "</data_type>");
						//outputln ("  - Extended OID: "+ ((ObjectIdentifierValue)value).toDetailString());
						outputln ("  <description>" + escapeHTML(((SnmpType)type).getDescription()) + "</description>");
					}
				}
				outputln ("</node>");
			}
		}
		}
		*/
}