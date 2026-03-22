import { useState, useEffect } from '@wordpress/element';
import { DndContext, closestCenter, KeyboardSensor, PointerSensor, useSensor, useSensors } from '@dnd-kit/core';
import { SortableContext, sortableKeyboardCoordinates, verticalListSortingStrategy, arrayMove, useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';

function SortableFieldItem({ field, idx, onRemove }) {
    const { attributes, listeners, setNodeRef, transform, transition } = useSortable({ id: field.id });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
        display: 'flex',
        justifyContent: 'space-between',
        alignItems: 'center',
        background: '#f6f7f7',
        border: '1px solid #ccd0d4',
        padding: '15px',
        borderRadius: '4px',
        marginBottom: '10px',
        cursor: 'grab'
    };

    return (
        <div ref={setNodeRef} style={style} {...attributes} {...listeners}>
            <div>
                <strong>{field.label}</strong> <small>({field.type})</small>
            </div>
            <div style={{display: 'flex', gap: '5px'}}>
                <button className="button button-small button-link-delete" style={{color: '#d63638', marginLeft: '10px'}} onPointerDown={(e) => { e.stopPropagation(); onRemove(idx); }}>Remove</button>
            </div>
        </div>
    );
}

const FormBuilder = ({ formId, onBack }) => {
    const [form, setForm] = useState({ title: 'New Form', config: { fields: [] } });
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        if (formId) {
            setLoading(true);
            const apiPath = window.wpApiSettings ? `${window.wpApiSettings.root}artemisia/v1/forms/${formId}` : `/wp-json/artemisia/v1/forms/${formId}`;
            // NOTE: We haven't built the GET /forms/{id} PHP endpoint yet, but this is the structure
            fetch(apiPath)
                .then(res => res.json())
                .then(data => {
                    setForm(data);
                    setLoading(false);
                })
                .catch(err => {
                    console.error('Error fetching form details', err);
                    setLoading(false);
                });
        }
    }, [formId]);

    // Field Types definition
    const availableFields = [
        { type: 'text', label: 'Text Input' },
        { type: 'email', label: 'Email' },
        { type: 'textarea', label: 'Textarea' },
        { type: 'select', label: 'Select' }
    ];

    const addField = (fieldType) => {
        const fieldDef = availableFields.find(f => f.type === fieldType);
        if (!fieldDef) return;

        const newField = {
            id: 'field_' + Date.now(),
            type: fieldDef.type,
            label: fieldDef.label,
            required: false
        };

        setForm(prev => ({
            ...prev,
            config: {
                ...prev.config,
                fields: [...(prev.config.fields || []), newField]
            }
        }));
    };

    const removeField = (index) => {
        setForm(prev => {
            const newFields = [...prev.config.fields];
            newFields.splice(index, 1);
            return {
                ...prev,
                config: { ...prev.config, fields: newFields }
            };
        });
    };

    const sensors = useSensors(
        useSensor(PointerSensor),
        useSensor(KeyboardSensor, { coordinateGetter: sortableKeyboardCoordinates })
    );

    const handleDragEnd = (event) => {
        const { active, over } = event;
        if (over && active.id !== over.id) {
            setForm((prev) => {
                const oldIndex = prev.config.fields.findIndex(f => f.id === active.id);
                const newIndex = prev.config.fields.findIndex(f => f.id === over.id);
                return {
                    ...prev,
                    config: { ...prev.config, fields: arrayMove(prev.config.fields, oldIndex, newIndex) }
                };
            });
        }
    };

    const handleSave = () => {
        const apiPath = window.wpApiSettings ? `${window.wpApiSettings.root}artemisia/v1/forms${formId ? '/' + formId : ''}` : `/wp-json/artemisia/v1/forms${formId ? '/' + formId : ''}`;
        
        const payload = {
            title: form.title,
            config: form.config,
            status: form.status || 'published'
        };

        const headers = { 'Content-Type': 'application/json' };
        if (window.wpApiSettings && window.wpApiSettings.nonce) {
            headers['X-WP-Nonce'] = window.wpApiSettings.nonce;
        }

        fetch(apiPath, {
            method: 'POST',
            headers: headers,
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            if (data.id) {
                alert('Form saved successfully!');
                if (!formId) {
                    // Navigate back or update to edit mode
                    onBack(); 
                }
            } else {
                alert('Error saving form: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(err => {
            console.error('Save error', err);
            alert('Failed to save form.');
        });
    };

    if (loading) return <p>Loading Form Data...</p>;

    return (
        <div className="artemisia-builder-container">
            <header className="builder-header">
                <div className="header-left">
                    <button className="button" onClick={onBack}>&larr; Back to List</button>
                    <h2>{formId ? `Editing: ${form.title}` : 'Creating New Form'}</h2>
                </div>
                <div className="header-right">
                    <button className="button button-primary" onClick={handleSave}>Save Form</button>
                </div>
            </header>

            <div className="builder-workspace">
                <aside className="builder-sidebar-left">
                    <h3>Available Fields</h3>
                    <div className="field-types-list">
                        {availableFields.map(f => (
                            <button key={f.type} className="button" onClick={() => addField(f.type)}>{f.label} +</button>
                        ))}
                    </div>
                </aside>

                <main className="builder-canvas">
                    <h3>Form Canvas</h3>
                    <div className="canvas-dropzone">
                        {form.config.fields && form.config.fields.length > 0 ? (
                            <DndContext sensors={sensors} collisionDetection={closestCenter} onDragEnd={handleDragEnd}>
                                <SortableContext items={form.config.fields.map(f => f.id)} strategy={verticalListSortingStrategy}>
                                    {form.config.fields.map((field, idx) => (
                                        <SortableFieldItem key={field.id} field={field} idx={idx} onRemove={removeField} />
                                    ))}
                                </SortableContext>
                            </DndContext>
                        ) : (
                            <p className="empty-canvas-message">Drag and drop fields here to start building your form.</p>
                        )}
                    </div>
                </main>

                <aside className="builder-sidebar-right">
                    <h3>Field Settings</h3>
                    <p className="help-text">Select a field in the canvas to edit its properties.</p>
                    {/* Settings UI will go here */}
                </aside>
            </div>
        </div>
    );
};

export default FormBuilder;
