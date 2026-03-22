import { useState, useEffect } from '@wordpress/element';
import FormBuilder from './components/FormBuilder';

const App = () => {
    const [forms, setForms] = useState([]);
    const [loading, setLoading] = useState(true);
    const [currentView, setCurrentView] = useState('list'); // 'list' or 'builder'
    const [selectedFormId, setSelectedFormId] = useState(null);

    // Fetch the forms list from our created REST API on load
    useEffect(() => {
        // wpApiSettings is globally provided by WordPress when we enqueue the script
        const apiPath = window.wpApiSettings ? window.wpApiSettings.root + 'artemisia/v1/forms' : '/wp-json/artemisia/v1/forms';
        
        fetch(apiPath)
            .then(res => res.json())
            .then(data => {
                setForms(data);
                setLoading(false);
            })
            .catch(err => {
                console.error('Error fetching forms:', err);
                setLoading(false);
            });
    }, []);

    const handleCreateNew = () => {
        setSelectedFormId(null);
        setCurrentView('builder');
    };

    const handleEdit = (id) => {
        setSelectedFormId(id);
        setCurrentView('builder');
    };

    const handleBackToList = () => {
        setCurrentView('list');
        // Optional: refresh forms list here
    };

    if (currentView === 'builder') {
        return <FormBuilder formId={selectedFormId} onBack={handleBackToList} />;
    }

    return (
        <div className="artemisia-forms-dashboard">
            <header className="artemisia-header">
                <h1>Artemisia Forms Builder</h1>
                <button className="button button-primary" onClick={handleCreateNew}>Create New Form</button>
            </header>
            
            <main>
                {loading ? (
                    <p>Loading forms...</p>
                ) : forms.length === 0 ? (
                    <div className="artemisia-empty-state">
                        <p>No forms found. Create your first form to get started!</p>
                    </div>
                ) : (
                    <table className="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Shortcode</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {forms.map(form => (
                                <tr key={form.id}>
                                    <td><strong>{form.title}</strong></td>
                                    <td>{form.status}</td>
                                    <td><code>[artemisia_form id="{form.id}"]</code></td>
                                    <td>
                                        <button className="button button-small" onClick={() => handleEdit(form.id)}>Edit</button>
                                        <button className="button button-small button-link-delete" style={{color: '#d63638', marginLeft: '10px'}}>Delete</button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                )}
            </main>
        </div>
    );
};

export default App;
