# Images Directory Structure

This directory contains all product and media images for the Farmers Mall application.

## Directory Structure

```
images/
└── products/          # Product images (vegetables, fruits, dairy, etc.)
    └── .gitkeep       # Keeps directory in Git

uploads/
├── products/          # Uploaded product images from retailers
│   └── .gitkeep
├── profile/           # User profile pictures (legacy)
│   └── .gitkeep
└── profiles/          # User profile pictures (current)
    └── .gitkeep
```

## Important Notes for Team Members

### After Pulling the Repository

1. **Product Images**: The `images/products/` folder contains 82+ product images that should be automatically pulled with the repository.

2. **Upload Folders**: The `uploads/` subdirectories are tracked but their contents (user-uploaded files) are ignored by Git for privacy and file size reasons.

3. **Missing Images?** If product images aren't showing:
   - Make sure you pulled the latest changes: `git pull origin main`
   - Check that `images/products/` folder exists and contains .png/.jpg files
   - Verify the `.gitkeep` files are present in empty directories

### Image Path References

The application uses relative paths:
- From user pages: `../images/products/filename.png`
- From API/root: `images/products/filename.png`

### Adding New Product Images

1. Place images in `images/products/` folder
2. Use descriptive names (e.g., `fresh-tomatoes.jpg`)
3. Supported formats: .jpg, .jpeg, .png
4. Recommended size: 800x800px or smaller
5. Commit and push: `git add images/products/your-image.png && git commit -m "Add product image"`

### Troubleshooting

**Images not loading for your team?**
- Run: `git add images/products/ -f` (to force add if needed)
- Verify `.gitignore` allows product images: `!images/products/.gitkeep`
- Check file permissions on server/local machine

**Profile pictures not working?**
- The profile upload folders are intentionally empty in Git
- Each environment generates its own profile pictures
- Make sure `uploads/profile/` and `uploads/profiles/` folders exist with proper permissions

## Team Setup Checklist

After cloning the repository, ensure:
- [ ] `images/products/` exists and has 80+ images
- [ ] `uploads/products/` exists (can be empty)
- [ ] `uploads/profile/` exists (can be empty)  
- [ ] `uploads/profiles/` exists (can be empty)
- [ ] All folders have proper read/write permissions
- [ ] Web server can access these directories

## Need Help?

If images still aren't showing after pulling:
1. Check browser console for 404 errors
2. Verify file paths in the code match actual file names
3. Ensure web server has permission to serve images from these directories
4. Contact the team lead for assistance
