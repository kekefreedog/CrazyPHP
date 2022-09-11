# Create Folder Structure

## Write Template

> Noticed that you can create a Json, Yaml or Php file, you just need to follow the structure of the object.

The create the bellow structure...

```
@app_root/
├─ Folder1/
├─ Folder2/
│  ├─ Subfolder2/
│  ├─ doc.txt
├─ doc.txt
```

...you have to write this in your schema folder :

```yaml
Structure:
  "@app_root":
    folders:
        Folder1: null
        Folder2:
            folders:
                Subfolder2: null
            files:
                doc.txt: null
    files:
        doc.txt: null
```

## Create complex file

If you null is find in **file**, the script will create an empty file. 

### Just copy

In this exemple, the script will copy the file found in the **source** field

```yaml
Structure:
  "@app_root":
    folders:
      docker:
        folders:
          nginx:
            files:
              nginx.conf:
                source: "@crazyphp_root/resources/Docker/docker/nginx/nginx.conf.hbs"
```

### Use Template

But you can dynamically create a file using template engine like HandlbarsJS...

Here an exemple : 

```yaml
Structure:
  "@app_root":
    folders:
      docker:
        folders:
          nginx:
            files:
              nginx.conf:
                source: "@crazyphp_root/resources/Docker/docker/nginx/nginx.conf.hbs"
                engine: "CrazyPHP\\Library\\Template\\Handlebars"
```

- `source` : Define the hbs file to use
- `engine` : Define the classe PHP of the template engine to use (You have to write `\\` because of yaml syntax)
