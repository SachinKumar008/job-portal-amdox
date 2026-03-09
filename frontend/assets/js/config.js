function getAPIBase() {
    const hostname = window.location.hostname;
    
    // Production (Render)
    if (hostname.includes('onrender.com')) {
      // Same domain - use relative path
      return window.location.origin + '/backend';
    }
    
    // Local development
    if (hostname === 'localhost' || hostname === '127.0.0.1') {
      const pathParts = window.location.pathname.split('/').filter(p => p);
      const projectFolder = pathParts[0];
      
      if (projectFolder) {
        return `http://localhost/${projectFolder}/backend`;
      }
      
      return 'http://localhost/job-listing-portal/backend';
    }
    
    return '/backend';
  }
  
  window.API_BASE = getAPIBase();
  console.log('🔧 API Base URL:', window.API_BASE);